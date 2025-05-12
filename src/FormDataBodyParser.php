<?php

namespace VMorozov\LaravelHttpClientRequestsLogger;

class FormDataBodyParser
{
    public function parseMultipartFormData(string $dataString): array
    {
        $parsedData = [];
        $firstLineEnd = strpos($dataString, "\r\n");
        if ($firstLineEnd === false) {
            $firstLineEnd = strpos($dataString, "\n");
        }
        if ($firstLineEnd === false) {
            return []; // Not a valid format or empty
        }

        $boundary = substr($dataString, 0, $firstLineEnd);
        if (strpos($boundary, '--') !== 0) {
            // Attempt to find the boundary if the first line isn't it directly
            // This might happen if there's a preamble.
            // For the provided example, the boundary is clearly on the first line.
            // A more robust parser would get the boundary from the Content-Type header.
            // Let's assume the provided string starts with the boundary.
            // The boundary string itself is what's *between* "--" and the line ending.
            // So, if the line is "--boundary_string", then "boundary_string" is not the boundary,
            // but "--boundary_string" is.
        }

        // 2. Split the string into parts based on the boundary.
        // The actual parts are separated by the boundary string.
        // The last part will end with the boundary followed by "--".
        $parts = explode($boundary, $dataString);
        array_shift($parts); // Remove the part before the first boundary
        array_pop($parts);   // Remove the part after the last boundary (which is just "--\r\n")

        foreach ($parts as $part) {
            // Remove leading/trailing newlines from each part
            $part = trim($part);

            if (empty($part) || $part === '--') {
                continue;
            }

            // 3. For each part, extract headers and value.
            // Parts are separated by a double newline (\r\n\r\n or \n\n)
            $headerAndValue = preg_split("/\r\n\r\n|\n\n/", $part, 2);
            if (count($headerAndValue) !== 2) {
                continue; // Malformed part
            }

            $headersStr = $headerAndValue[0];
            $value = $headerAndValue[1];

            $name = null;
            if (preg_match('/Content-Disposition:.*?name="([^"]*)"/i', $headersStr, $matches)) {
                $name = $matches[1];
            }

            if ($name !== null) {
                $parsedData[$name] = $value;
            }
        }

        return $parsedData;
    }
}
