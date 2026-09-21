<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\Log;

class PdfUploadSecurity
{
    protected $pdfParser;

    public function __construct()
    {
        $this->pdfParser = new Parser();
    }

    public function handle(Request $request, Closure $next, $fields = null)
    {
        $fields = $fields ? explode(',', $fields) : null;

        // If fields are given, only scan those files
        $files = $fields ? array_map(fn($f) => $request->file($f), $fields) : $request->allFiles();

        foreach ($files as $file) {
            if (is_array($file)) {
                foreach ($file as $f) {
                    $error = $this->scanAndSanitize($f);
                    if ($error) return $error;
                }
            } elseif ($file) {
                $error = $this->scanAndSanitize($file);
                if ($error) return $error;
            }
        }

        return $next($request);
    }

    protected function scanAndSanitize($file)
    {
        if (!$file->isValid()) return null;

        $extension = strtolower($file->getClientOriginalExtension());
        $scanExtensions = ['pdf', 'docx', 'ppt', 'pptx'];

        if (!in_array($extension, $scanExtensions)) return null; // skip unsupported types

        try {
            if($extension === 'pdf')
			{
                // PDF scanning
                $pdf = $this->pdfParser->parseFile($file);
                $text = strtolower($pdf->getText());

                $badPatterns = [
                    'javascript','/javascript','/js','script','<script','</script','alert(','eval(',
                    'onload=','onerror=','launch','submit-form','openaction','/action','/annot',
                    '/richmedia','/xfa',
                ];

                foreach ($badPatterns as $pattern) {
                    if (strpos($text, $pattern) !== false) {
                        return $this->errorResponse('PDF contains potentially harmful content.');
                    }
                }

                /*
                $originalPath = $file->getPathname();
                $tempPath = tempnam(sys_get_temp_dir(), 'pdf_') . '.pdf';
                $gsPath = 'C:\\Program Files\\gs\\gs10.06.0\\bin\\gswin64c';
                $command = "\"$gsPath\" -sDEVICE=pdfwrite -dPDFSETTINGS=/prepress -dNOPAUSE -dBATCH -dSAFER -sOutputFile=\"$tempPath\" \"$originalPath\"";

                exec($command, $output, $returnVar);

                if ($returnVar !== 0 || !file_exists($tempPath)) {
                    unlink($tempPath);
                    Log::error('The PDF contains unsupported content: '.$file->getClientOriginalName());
                    return $this->errorResponse('PDF could not be sanitized. Upload failed.');
                }

                copy($tempPath, $originalPath);
                unlink($tempPath);
				*/

            }
			elseif (in_array($extension, ['docx', 'ppt', 'pptx']))
			{
                // Scan DOCX/PPTX via ZipArchive
                $zip = new \ZipArchive();
                if ($zip->open($file->getPathname()) === true) {
                    $textContent = '';
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $textContent .= strtolower($zip->getFromIndex($i) ?: '');
                    }
                    $zip->close();

                    $badPatterns = ['javascript','<script','eval(','onload=','onerror='];
                    foreach ($badPatterns as $pattern) {
                        if (strpos($textContent, $pattern) !== false) {
                            return $this->errorResponse('File contains potentially harmful content.');
                        }
                    }
                } else {
                    Log::error('Failed to read archive: '.$file->getClientOriginalName());
                    return $this->errorResponse('File could not be scanned. Upload failed.');
                }
            }
        } catch (\Exception $e) {
            Log::error('File scan/sanitization error: '.$e->getMessage());
            return $this->errorResponse('Invalid file. Upload failed.');
        }

        return null;
    }

    /**
     * Return appropriate error based on request type
     */
    protected function errorResponse($message)
    {
        if (request()->ajax() || request()->wantsJson()) {
            //return response()->json(['success' => false, 'message' => $message], 422);
			return response()->json([
				'errors' => ['invalid_file'=>[$message]]
			], 422);				
			
        }

        return redirect()->back()->withInput()->with('fail', $message);
    }
}