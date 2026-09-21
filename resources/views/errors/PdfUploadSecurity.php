<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\Log;

class PdfUploadSecurity
{
    protected $pdfParser;
    protected $gsPath;

    public function __construct()
    {
        $this->pdfParser = new Parser();
        // Adjust this path if Ghostscript is elsewhere
        $this->gsPath = '/usr/bin/gs';
    }

    public function handle(Request $request, Closure $next, $fields = null)
    {
        $fields = $fields ? explode(',', $fields) : null;

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

        if (!in_array($extension, $scanExtensions)) return null;

        try {
            if ($extension === 'pdf') {
                // Step 1: Create a temporary copy
                $tempFile = tempnam(sys_get_temp_dir(), 'pdf_scan');
                copy($file->getPathname(), $tempFile);

                // Step 2: Sanitize via Ghostscript
                $gsOutput = tempnam(sys_get_temp_dir(), 'pdf_sanitized') . '.pdf';
                $cmd = escapeshellcmd(
                    "{$this->gsPath} -sDEVICE=pdfwrite -dCompatibilityLevel=1.7 -dNOPAUSE -dBATCH -sOutputFile=$gsOutput $tempFile"
                );
                exec($cmd, $output, $returnVar);

                if ($returnVar !== 0 || !file_exists($gsOutput)) {
                    Log::error("Ghostscript failed for {$file->getClientOriginalName()}");
                    unlink($tempFile);
                    return $this->errorResponse('Failed to sanitize PDF.');
                }

                // Step 3: Parse sanitized PDF
                $pdfContent = file_get_contents($gsOutput);
                $pdf = $this->pdfParser->parseContent($pdfContent);
                $text = strtolower($pdf->getText() ?? '');

                // Step 4: Scan for harmful patterns
                $badPatterns = [
                    'javascript','/javascript','/js','script','<script','</script','alert(','eval(',
                    'onload=','onerror=','launch','submit-form','openaction','/action','/annot',
                    '/richmedia','/xfa',
                ];


                foreach ($badPatterns as $pattern) {
                    if (strpos($text, $pattern) !== false) {
                        unlink($tempFile);
                        unlink($gsOutput);
                        return $this->errorResponse('PDF contains potentially harmful content. Please upload another file.');
                    }
                }

                // Step 5: Cleanup
                unlink($tempFile);
                unlink($gsOutput);

            } elseif (in_array($extension, ['docx', 'ppt', 'pptx'])) {
                // Existing scan logic for DOCX/PPTX
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
                            return $this->errorResponse('File contains potentially harmful content. Please upload another file.');
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

    protected function errorResponse($message)
    {
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'errors' => ['invalid_file'=>[$message]]
            ], 422);
        }

        return redirect()->back()->withInput()->with('fail', $message);
    }
}