<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BlockScriptTags
{
    // Fields that use TinyMCE (rich text editor)
    protected $htmlAllowedFields = [
        'projectobjective','prebidenquiry','prebidreply', 'aboutproject','scope', 'scopeofwork', 'anyother', 
        'evaluationprocess', 'termsandcondition', 'criticalinformation', 'documentrequired',
    ];
	
    public function handle(Request $request, Closure $next)
    {
        $data = $request->all();

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $index => $item) {
                    $decodedItem = is_string($item) ? html_entity_decode($item, ENT_QUOTES | ENT_HTML5, 'UTF-8') : $item;

                    if ($this->containsScript($decodedItem)) {
                        return $this->scriptDetectedResponse($key);
                    }

                    $value[$index] = $this->sanitizeContent($decodedItem);
                }
                $data[$key] = $value;
                continue;
            }

            $decodedValue = is_string($value) ? html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8') : $value;

            // Check for script tags for all fields
            if ($this->containsScript($decodedValue)) {
                return $this->scriptDetectedResponse($key);
            }

            if (in_array($key, $this->htmlAllowedFields)) {
                // Sanitize TinyMCE content
                $data[$key] = $this->sanitizeEditorContent($decodedValue);
            } else {
                // Reject HTML for non-TinyMCE fields
                if ($this->containsHtmlTags($decodedValue)) {
                    return response()->json([
                        'message' => 'Invalid input detected.',
                        'errors' => [
                            $key => ['HTML tags are not allowed in this field.']
                        ]
                    ], 422);
                }

                $data[$key] = is_string($decodedValue) ? strip_tags($decodedValue) : $decodedValue;
            }

			if(!in_array($key, $this->htmlAllowedFields))
			{
				if($this->containsAngleBrackets($decodedValue))
				{
					return response()->json([
						'message' => 'Invalid input detected.',
						'errors' => [
							$key => ['The field cannot contain < or > characters.']
						]
					], 422);
				}

				$data[$key] = is_string($decodedValue) ? strip_tags($decodedValue) : $decodedValue;
			}
			
        }

        $request->merge($data);
        return $next($request);
    }
	/*
    private function containsScript($value)
    {
        if (!is_string($value)) return false;

        if (preg_match('/<\s*script\b/i', $value)) return true;

        if (preg_match('/javascript:/i', $value)) return true;

        if (preg_match('/on\w+\s*=/i', $value)) return true;

        return false;
    }

    private function containsHtmlTags($value)
    {
        if (!is_string($value)) return false;
        return preg_match('/<.*?>/', $value) > 0;
    }

    private function sanitizeEditorContent($value)
    {
        $allowedTags = '<b><strong><i><em><u><p><a><ul><ol><li><span><br>';
        return is_string($value) ? strip_tags($value, $allowedTags) : $value;
    }

    private function sanitizeContent($value)
    {
        $allowedTags = '<b><strong><i><em><u><p><a><ul><ol><li><span><br>';
        return is_string($value) ? strip_tags($value, $allowedTags) : $value;
    }

    private function scriptDetectedResponse($key)
    {
        return response()->json([
            'message' => 'Invalid input detected.',
            'errors' => [
                $key => ['Script tags or malicious content are not allowed']
            ]
        ], 422);
    }

	private function containsAngleBrackets($value)
	{
		if (!is_string($value)) return false;
		return strpos($value, '<') !== false || strpos($value, '>') !== false;
	}	
	*/
}
