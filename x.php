<?php

// auto-fixer.php

/**
 * Auto generate RenameClassRector map based on wrong class names
 * and real classes found in app/ and Modules/ folders.
 */

// List of wrong classes
$wrongClasses = [
    'Yajra\\Datatables\\DatatablesServiceProvider',
    'App\\Http\\Controllers\\Exception',
    'Modules\\InventoryManagement\\Http\\Controllers\\Exception',
    'SimpleSoftwareIO\\QrCode\\Facades\\QrCode',
    'Spatie\\Dropbox\\Client',
    'GuzzleHttp\\Psr7\\Request',
    'Endroid\\QrCode\\QrCode',
    'Eexception',
];

// Folders to search
$searchFolders = [
    __DIR__ . '/app',
    __DIR__ . '/routes',
    __DIR__ . '/resources',
    __DIR__ . '/config',
    __DIR__ . '/Modules',
];

// Helper: extract full namespace from PHP file
function getNamespaceFromFile(string $filepath): ?string
{
    $content = file_get_contents($filepath);
    if (preg_match('/namespace\s+(.+?);/', $content, $matches)) {
        return trim($matches[1]);
    }
    return null;
}

// Helper: find class definition
function findClass(string $shortClassName, array $folders): ?string
{
    foreach ($folders as $folder) {
        $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder));
        foreach ($rii as $file) {
            if ($file->isDir()) continue;
            if (pathinfo($file->getPathname(), PATHINFO_EXTENSION) !== 'php') continue;

            $content = file_get_contents($file->getPathname());
            if (preg_match('/(class|interface|trait)\s+' . preg_quote($shortClassName, '/') . '\b/', $content)) {
                $namespace = getNamespaceFromFile($file->getPathname());
                if ($namespace) {
                    return $namespace . '\\' . $shortClassName;
                }
            }
        }
    }
    return null;
}

// Main script
$renameMap = [];

foreach ($wrongClasses as $wrongClass) {
    $shortName = basename(str_replace('\\', '/', $wrongClass));
    $foundFullName = findClass($shortName, $searchFolders);

    if ($foundFullName) {
        $renameMap[$wrongClass] = $foundFullName;
    } else {
        echo "[NOT FOUND] " . $wrongClass . PHP_EOL;
    }
}

// Output
echo "\n\n";
echo "Paste this inside your rector.php:\n\n";
echo "\$rectorConfig->ruleWithConfiguration(Rector\\Renaming\\Rector\\Name\\RenameClassRector::class, [\n";
foreach ($renameMap as $wrong => $correct) {
    echo "    '$wrong' => '$correct',\n";
}
echo "]);\n";
