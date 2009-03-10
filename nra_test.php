<?php

$NRA_FILE = "/wwws/cgi/ccnmtl/draft/wikispaces-admin/sample.txt";
$uni = $_SERVER['REMOTE_USER'];

$cmd = "grep $uni $NRA_FILE";
// put each line into an array
$output = explode("\n", shell_exec($cmd));

//$output = shell_exec("ls -l");
$nra_array = array();
foreach ($output as $line) {
	if (!$line) {
		continue;
	}
	$nra_array[] = parseCsvLine($line);
	//$nra_array[] = $line;
}

//print_r($nra_array);

$nra_classes = array();
foreach ($nra_array as $nra_row) {
	$nra_classes[] = $nra_row[3];	
}

print "NRA classes for $uni:<br>";
print_r($nra_classes);

function parseCsvLine($str) {
        $delimier = ',';
        $qualifier = "'";
        $qualifierEscape = '\\';

        $fields = array();
        while (strlen($str) > 0) {
            if ($str{0} == $delimier)
                $str = substr($str, 1);
            if ($str{0} == $qualifier) {
                $value = '';
                for ($i = 1; $i < strlen($str); $i++) {
                    if (($str{$i} == $qualifier) && ($str{$i-1} != $qualifierEscape)) {
                        $str = substr($str, (strlen($value) + 2));
                        $value = str_replace(($qualifierEscape.$qualifier), $qualifier, $value);
                        break;
                    }
                    $value .= $str{$i};
                }
            } else {
                $end = strpos($str, $delimier);
                $value = ($end !== false) ? substr($str, 0, $end) : $str;
                $str = substr($str, strlen($value));
            }
            $fields[] = $value;
        }
        return $fields;
}

?>