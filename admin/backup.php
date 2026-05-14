<?php
include 'includes/session.php';
include '../includes/conn.php';

set_time_limit(0);
ini_set('memory_limit', '512M');

date_default_timezone_set('Asia/Manila');

$backupName = "backup_" . date("Y-m-d_H-i-s") . ".zip";
$tmpFile = sys_get_temp_dir() . "/" . $backupName;

$zip = new ZipArchive();

if ($zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die("Cannot create backup.");
}

if (ob_get_length()) ob_end_clean();

/* =========================
   ROOT PATH (PAYROLL SYSTEM)
========================= */

$rootPath = realpath(__DIR__ . "/..");

/* =========================
   1. BACKUP FILES FOLDER
========================= */

$filesPath = $rootPath;

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($filesPath, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {

    if ($file->isFile()) {

        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($rootPath) + 1);

        // skip backup files
        if (strpos($relativePath, 'backup_') === 0) continue;
        if (strpos($relativePath, '.zip') !== false) continue;

        $zip->addFile($filePath, "files/" . $relativePath);
    }
}

/* =========================
   2. DATABASE BACKUP
========================= */

$tables = [];
$result = $conn->query("SHOW TABLES");

while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
}

$sqlDump = "SET FOREIGN_KEY_CHECKS=0;\n\n";

foreach ($tables as $table) {

    $create = $conn->query("SHOW CREATE TABLE `$table`")->fetch_array();
    $sqlDump .= "DROP TABLE IF EXISTS `$table`;\n";
    $sqlDump .= $create[1] . ";\n\n";

    $data = $conn->query("SELECT * FROM `$table`");

    while ($row = $data->fetch_assoc()) {

        $values = [];

        foreach ($row as $v) {
            if (is_null($v)) {
                $values[] = "NULL";
            } else {
                $values[] = "'" . $conn->real_escape_string($v) . "'";
            }
        }

        $sqlDump .= "INSERT INTO `$table` VALUES(" . implode(",", $values) . ");\n";
    }

    $sqlDump .= "\n";
}

$sqlDump .= "SET FOREIGN_KEY_CHECKS=1;\n";

/* Put DB inside folder like VoteSystem */
$zip->addFromString("database/database.sql", $sqlDump);

/* =========================
   FINALIZE ZIP
========================= */

$zip->close();

/* =========================
   DOWNLOAD
========================= */

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $backupName . '"');
header('Content-Length: ' . filesize($tmpFile));
header('Pragma: no-cache');
header('Expires: 0');

readfile($tmpFile);
unlink($tmpFile);

exit;
?>