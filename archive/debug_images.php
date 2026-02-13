<!DOCTYPE html>
<html>
<head>
    <title>Image Debug Test (Root Directory)</title>
</head>
<body>
    <h1>Testing Images (Root Directory)</h1>
    <p>We moved images to the main folder. If you see them below, it works.</p>
    
    <h2>Logo2.png</h2>
    <img src="Logo2.png" alt="Logo2" style="border: 2px solid red; max-width: 200px;">
    
    <h2>Logo.png</h2>
    <img src="Logo.png" alt="Logo" style="border: 2px solid blue; max-width: 200px;">
    
    <h2>Collage.png</h2>
    <img src="Collage.png" alt="Collage" style="border: 2px solid green; max-width: 500px;">

    <hr>
    
    <h3>File info:</h3>
    <pre><?php
    $files = ['Logo2.png', 'Logo.png', 'Collage.png'];
    foreach ($files as $f) {
        if (file_exists($f)) {
            echo "$f exists (" . filesize($f) . " bytes)\n";
        } else {
            echo "$f DOES NOT EXIST\n";
        }
    }
    ?></pre>
</body>
</html>
