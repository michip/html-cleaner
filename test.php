<?php
/**
 * Created by IntelliJ IDEA.
 * User: User
 * Date: 01.06.2016
 * Time: 11:48
 */

include 'library/HTMLCleaner.php';

$htmlCleaner = new HTMLCleaner();
echo "<xmp>";
echo $htmlCleaner->clean(file_get_contents('samplehtml.html'));
echo "</xmp>";