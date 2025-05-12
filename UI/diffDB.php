<!DOCTYPE html>
<html>
    <head>
        <style>
            :root {
                --text-color: #111827;
                --text-light: #6b7280;
                --border-color: #e5e7eb;
                --highlight-color: #fef08a;
                --success-color: #22c55e;
                --error-color: #dc2626;
                --card-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            }
            
            * {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }
            
            .diff-section {
                display: flex;
                flex-wrap: wrap;
                gap: 2rem;
                justify-content: space-between;
                font-family: 'Inter', sans-serif;
                color: var(--text-color);
                line-height: 1.6;
            }
            
            .block-section {
                flex: 1 1 45%;
                min-width: 300px;
            }
            
            .block-section pre {
                background: #ffffff;
                border-radius: 0.75rem;
                padding: 1rem;
                box-shadow: var(--card-shadow);
                border: 1px solid var(--border-color);
                font-size: 0.9rem;
                line-height: 1.5;
                white-space: pre-wrap;
                word-wrap: break-word;
                margin-bottom: 1rem;
                position: relative;
                transition: transform 0.3s ease;
            }
            
            .block-section pre:hover {
                transform: translateY(-5px);
                box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
            }
            
            .block-section pre::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: linear-gradient(135deg, rgba(79, 70, 229, 0.05), transparent);
                border-radius: 0.75rem;
                z-index: -1;
            }
            
            .block-section h3 {
                font-size: 1.1rem;
                font-weight: 600;
                margin-bottom: 0.5rem;
            }
            
            .block-section h3.original {
                color: var(--success-color);
            }
            
            .block-section h3.corrupted {
                color: var(--error-color);
            }
            
            .highlight-diff {
                background-color: var(--highlight-color);
                padding: 0.1rem 0.3rem;
                border-radius: 3px;
                display: block;
            }
            
            @media (max-width: 768px) {
                .block-section {
                    flex: 1 1 100%;
                }
            }
        </style>
    </head>
    <body>
<?php

// A class containing functions for computing diffs and formatting the output.
class Diff{

    // define the constants
    const UNMODIFIED = 0;
    const DELETED    = 1;
    const INSERTED   = 2;

    /* Returns the diff for two strings. The return value is an array, each of
     * whose values is an array containing two values: a line (or character, if
     * $compareCharacters is true), and one of the constants DIFF::UNMODIFIED (the
     * line or character is in both strings), DIFF::DELETED (the line or character
     * is only in the first string), and DIFF::INSERTED (the line or character is
     * only in the second string). The parameters are:
     *
     * $string1           - the first string
     * $string2           - the second string
     * $compareCharacters - true to compare characters, and false to compare
     *                      lines; this optional parameter defaults to false
     */
    public static function compare(
        $string1, $string2, $compareCharacters = false){

        // initialise the sequences and comparison start and end positions
        $start = 0;
        if ($compareCharacters){
            $sequence1 = $string1;
            $sequence2 = $string2;
            $end1 = strlen($string1) - 1;
            $end2 = strlen($string2) - 1;
        }else{
            $sequence1 = preg_split('/\R/', $string1);
            $sequence2 = preg_split('/\R/', $string2);
            $end1 = count($sequence1) - 1;
            $end2 = count($sequence2) - 1;
        }

        // skip any common prefix
        while ($start <= $end1 && $start <= $end2
            && $sequence1[$start] == $sequence2[$start]){
            $start ++;
        }

        // skip any common suffix
        while ($end1 >= $start && $end2 >= $start
            && $sequence1[$end1] == $sequence2[$end2]){
            $end1 --;
            $end2 --;
        }

        // compute the table of longest common subsequence lengths
        $table = self::computeTable($sequence1, $sequence2, $start, $end1, $end2);

        // generate the partial diff
        $partialDiff =
            self::generatePartialDiff($table, $sequence1, $sequence2, $start);

        // generate the full diff
        $diff = array();
        for ($index = 0; $index < $start; $index ++){
            $diff[] = array($sequence1[$index], self::UNMODIFIED);
        }
        while (count($partialDiff) > 0) $diff[] = array_pop($partialDiff);
        for ($index = $end1 + 1;
            $index < ($compareCharacters ? strlen($string1) : count($sequence1));
            $index ++){
            $diff[] = array($sequence1[$index], self::UNMODIFIED);
        }

        // return the diff
        return $diff;

    }

    /* Returns the diff for two files. The parameters are:
     *
     * $file1             - the path to the first file
     * $file2             - the path to the second file
     * $compareCharacters - true to compare characters, and false to compare
     *                      lines; this optional parameter defaults to false
     */
    public static function compareFiles(
        $file1, $file2, $compareCharacters = false){

        // return the diff of the files
        return self::compare(
            file_get_contents($file1),
            file_get_contents($file2),
            $compareCharacters);

    }

    /* Returns the table of longest common subsequence lengths for the specified
     * sequences. The parameters are:
     *
     * $sequence1 - the first sequence
     * $sequence2 - the second sequence
     * $start     - the starting index
     * $end1      - the ending index for the first sequence
     * $end2      - the ending index for the second sequence
     */
    private static function computeTable(
        $sequence1, $sequence2, $start, $end1, $end2){

        // determine the lengths to be compared
        $length1 = $end1 - $start + 1;
        $length2 = $end2 - $start + 1;

        // initialise the table
        $table = array(array_fill(0, $length2 + 1, 0));

        // loop over the rows
        for ($index1 = 1; $index1 <= $length1; $index1 ++){

            // create the new row
            $table[$index1] = array(0);

            // loop over the columns
            for ($index2 = 1; $index2 <= $length2; $index2 ++){

                // store the longest common subsequence length
                if ($sequence1[$index1 + $start - 1]
                    == $sequence2[$index2 + $start - 1]){
                    $table[$index1][$index2] = $table[$index1 - 1][$index2 - 1] + 1;
                }else{
                    $table[$index1][$index2] =
                        max($table[$index1 - 1][$index2], $table[$index1][$index2 - 1]);
                }

            }
        }

        // return the table
        return $table;

    }

    /* Returns the partial diff for the specificed sequences, in reverse order.
     * The parameters are:
     *
     * $table     - the table returned by the computeTable function
     * $sequence1 - the first sequence
     * $sequence2 - the second sequence
     * $start     - the starting index
     */
    private static function generatePartialDiff(
        $table, $sequence1, $sequence2, $start){

        //  initialise the diff
        $diff = array();

        // initialise the indices
        $index1 = count($table) - 1;
        $index2 = count($table[0]) - 1;

        // loop until there are no items remaining in either sequence
        while ($index1 > 0 || $index2 > 0){

            // check what has happened to the items at these indices
            if ($index1 > 0 && $index2 > 0
                && $sequence1[$index1 + $start - 1]
                    == $sequence2[$index2 + $start - 1]){

                // update the diff and the indices
                $diff[] = array($sequence1[$index1 + $start - 1], self::UNMODIFIED);
                $index1 --;
                $index2 --;

            }elseif ($index2 > 0
                && $table[$index1][$index2] == $table[$index1][$index2 - 1]){

                // update the diff and the indices
                $diff[] = array($sequence2[$index2 + $start - 1], self::INSERTED);
                $index2 --;

            }else{

                // update the diff and the indices
                $diff[] = array($sequence1[$index1 + $start - 1], self::DELETED);
                $index1 --;

            }

        }

        // return the diff
        return $diff;

    }

    /* Returns a diff as a string, where unmodified lines are prefixed by '  ',
     * deletions are prefixed by '- ', and insertions are prefixed by '+ '. The
     * parameters are:
     *
     * $diff      - the diff array
     * $separator - the separator between lines; this optional parameter defaults
     *              to "\n"
     */
    public static function toString($diff, $separator = "\n"){

        // initialise the string
        $string = '';

        // loop over the lines in the diff
        foreach ($diff as $line){

            // extend the string with the line
            switch ($line[1]){
                case self::UNMODIFIED : $string .= '  ' . $line[0];break;
                case self::DELETED    : $string .= '- ' . $line[0];break;
                case self::INSERTED   : $string .= '+ ' . $line[0];break;
            }

            // extend the string with the separator
            $string .= $separator;

        }

        // return the string
        return $string;

    }

    /* Returns a diff as an HTML string, where unmodified lines are contained
     * within 'span' elements, deletions are contained within 'del' elements, and
     * insertions are contained within 'ins' elements. The parameters are:
     *
     * $diff      - the diff array
     * $separator - the separator between lines; this optional parameter defaults
     *              to '<br>'
     */
    public static function toHTML($diff, $separator = '<br>'){

        // initialise the HTML
        $html = '';

        // loop over the lines in the diff
        foreach ($diff as $line){

            // extend the HTML with the line
            switch ($line[1]){
                case self::UNMODIFIED : $element = 'span'; break;
                case self::DELETED    : $element = 'del';  break;
                case self::INSERTED   : $element = 'ins';  break;
            }
            $html .=
                '<' . $element . '>'
                . htmlspecialchars($line[0])
                . '</' . $element . '>';

            // extend the HTML with the separator
            $html .= $separator;

        }

        // return the HTML
        return $html;

    }

    /* Returns a diff as a side-by-side layout with floating cards. The parameters are:
     *
     * $diff        - the diff array
     * $indentation - indentation to add to every line of the generated HTML; this
     *                optional parameter defaults to ''
     * $separator   - the separator between lines; this optional parameter
     *                defaults to '<br>'
     */
    public static function toTable($diff, $indentation = '', $separator = '<br>'){

        // Parse original and corrupted data into blocks
        $original_blocks = [];
        $corrupted_blocks = [];
        $current_block = null;
        $block_diff = [];

        // Group the diff lines by block and separate original vs corrupted
        foreach ($diff as $line) {
            $line_text = $line[0];
            $line_type = $line[1];

            if (preg_match('/^\[.*\]$/', $line_text)) {
                $current_block = $line_text;
                $block_diff[$current_block] = [];
            } else {
                $block_diff[$current_block][] = [$line_text, $line_type];
            }
        }

        // Process each block to separate original and corrupted lines, highlighting differences
        foreach ($block_diff as $block => $lines) {
            $original_lines = [];
            $corrupted_lines = [];
            $i = 0;

            while ($i < count($lines)) {
                $line = $lines[$i];
                $line_text = $line[0];
                $line_type = $line[1];

                if ($line_type == self::UNMODIFIED) {
                    $original_lines[] = htmlspecialchars($line_text);
                    $corrupted_lines[] = htmlspecialchars($line_text);
                    $i++;
                } elseif ($line_type == self::DELETED && $i + 1 < count($lines) && $lines[$i + 1][1] == self::INSERTED) {
                    // Pair of deleted (original) and inserted (corrupted) lines
                    $deleted_line = $line_text;
                    $inserted_line = $lines[$i + 1][0];
                    $i += 2;

                    $original_lines[] = '<span class="highlight-diff">' . htmlspecialchars($deleted_line) . '</span>';
                    $corrupted_lines[] = '<span class="highlight-diff">' . htmlspecialchars($inserted_line) . '</span>';
                } elseif ($line_type == self::DELETED) {
                    $original_lines[] = '<span class="highlight-diff">' . htmlspecialchars($line_text) . '</span>';
                    $corrupted_lines[] = '';
                    $i++;
                } elseif ($line_type == self::INSERTED) {
                    $original_lines[] = '';
                    $corrupted_lines[] = '<span class="highlight-diff">' . htmlspecialchars($line_text) . '</span>';
                    $i++;
                }
            }

            $original_blocks[$block] = $original_lines;
            $corrupted_blocks[$block] = $corrupted_lines;
        }

        // Generate the side-by-side HTML with floating cards
        $html = $indentation . "<div class=\"diff-section\">\n";

        // Original column
        $html .= $indentation . "  <div class=\"block-section\">\n";
        foreach ($original_blocks as $block => $lines) {
            $html .= $indentation . "    <h3 class=\"original\">" . htmlspecialchars($block) . "</h3>\n";
            $html .= $indentation . "    <pre>" . implode("\n", $lines) . "</pre>\n";
        }
        $html .= $indentation . "  </div>\n";

        // Corrupted column
        $html .= $indentation . "  <div class=\"block-section\">\n";
        foreach ($corrupted_blocks as $block => $lines) {
            $html .= $indentation . "    <h3 class=\"corrupted\">" . htmlspecialchars($block) . "</h3>\n";
            $html .= $indentation . "    <pre>" . implode("\n", $lines) . "</pre>\n";
        }
        $html .= $indentation . "  </div>\n";

        $html .= $indentation . "</div>\n";

        return $html;
    }

    /* Returns the content of the cell, for use in the toTable function. The
     * parameters are:
     *
     * $diff        - the diff array
     * $indentation - indentation to add to every line of the generated HTML
     * $separator   - the separator between lines
     * $index       - the current index, passes by reference
     * $type        - the type of line
     */
    private static function getCellContent(
        $diff, $indentation, $separator, &$index, $type){

        // initialise the HTML
        $html = '';

        // loop over the matching lines, adding them to the HTML
        while ($index < count($diff) && $diff[$index][1] == $type){
            $html .=
                '<span>'
                . htmlspecialchars($diff[$index][0])
                . '</span>'
                . $separator;
            $index ++;
        }

        // return the HTML
        return $html;

    }

}

// compare two files line by line
$diff = Diff::compareFiles('blockchain.bak', 'blockchain.ini');
echo Diff::toTable($diff);

?>
    </body>
</html>