<?php
$uid=  uniqid("latex_", true);
$outpath="/tmp/$uid";
$workdir="simple/";
$filename="simple.tex";
$command="mkdir -p  $outpath && cd $workdir && pdflatex -output-directory $outpath  $filename";
echo nl2br("\n========BEGIN COMPILE==========\n".shell_exec($command)."\n========END COMPILE==========\n");
$cleanup="rm -fr $outpath";
//echo $command;
$pdffile=basename($filename, ".tex").'.pdf'.PHP_EOL;
$logfile=basename($filename, ".tex").'.log'.PHP_EOL;

echo "$outpath/$pdffile";
echo "$outpath/$logfile";
shell_exec($cleanup);
