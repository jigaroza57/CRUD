<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table = $_POST['table_name'];
    $fields = $_POST['fields'];
    $pk_index = $_POST['primary_key'];
    $action = isset($_POST['action']) ? $_POST['action'] : 'download';

    // Build SQL and PHP code
    $columns = [];
    $pk = '';
    foreach ($fields as $i => $f) {
        if (empty($f['name'])) continue;
        
        $type = $f['type'];
        $len = '';
        // Force mobile/phone fields to VARCHAR(10)
        if (preg_match('/^(mobile|phone)$/i', $f['name'])) {
            $type = 'VARCHAR';
            $f['length'] = '10';
            $len = "(10)";
        } elseif ($type == 'VARCHAR' && !empty($f['length'])) {
            $len = "({$f['length']})";
        } elseif ($type == 'DECIMAL' && !empty($f['length'])) {
            $len = "({$f['length']})";
        }
        
        $col = "`{$f['name']}` $type$len";
        
        if ($type == 'BOOL') {
            $col = "`{$f['name']}` TINYINT(1)";
        }
        
        if ($pk_index == $i) {
            $col .= " PRIMARY KEY AUTO_INCREMENT";
            $pk = $f['name'];
        }
        $columns[] = $col;
    }
    
    $sql = "CREATE TABLE `$table` (\n    " . implode(",\n    ", $columns) . "\n);";

    // Generate PHP code for CRUD
    $field_names = [];
    foreach ($fields as $f) {
        if (!empty($f['name'])) {
            $field_names[] = $f['name'];
        }
    }
    
    $fields_list = implode(", ", $field_names);
    $fields_q = implode(", ", array_map(function($f) { return ":$f"; }, $field_names));
    $update_set = implode(", ", array_map(function($f) { return "$f = :$f"; }, $field_names));

    // Database connection file
    $db_php = '<?php
// db.php - Database Connection
try {
    $host = "localhost";
    $dbname = "YOUR_DATABASE_NAME"; // Change this to your database name
    $username = "root"; // Change if needed
    $password = ""; // Change if needed
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>';

    // INSERT PHP
    $insert_params = [];
    foreach ($field_names as $f) {
        if ($f != $pk) {
            $insert_params[] = "        '$f' => \$_POST['$f']";
        }
    }
    
    $non_pk_fields = array_filter($field_names, function($f) use ($pk) { return $f != $pk; });
    $non_pk_list = implode(", ", $non_pk_fields);
    $non_pk_q = implode(", ", array_map(function($f) { return ":$f"; }, $non_pk_fields));
    
    $insert_php = '<?php
// insert.php - Add New Record
require "db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $stmt = $pdo->prepare("INSERT INTO ' . $table . ' (' . $non_pk_list . ') VALUES (' . $non_pk_q . ')");
        $stmt->execute([
' . implode(",\n", $insert_params) . '
        ]);
        
        echo "<script>
            alert(\'Record inserted successfully!\');
            window.location.href = \'index.php\';
        </script>";
    } catch (PDOException $e) {
        echo "<script>
            alert(\'Error: " . $e->getMessage() . "\');
            window.history.back();
        </script>";
    }
}
?>';

    // UPDATE PHP
    $update_params = [];
    foreach ($field_names as $f) {
        $update_params[] = "        '$f' => \$_POST['$f']";
    }
    
    $update_php = '<?php
// update.php - Update Record
require "db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $stmt = $pdo->prepare("UPDATE ' . $table . ' SET ' . $update_set . ' WHERE ' . $pk . ' = :pk");
        $data = [
' . implode(",\n", $update_params) . ',
            "pk" => $_POST["' . $pk . '"]
        ];
        $stmt->execute($data);
        
        echo "<script>
            alert(\'Record updated successfully!\');
            window.location.href = \'index.php\';
        </script>";
    } catch (PDOException $e) {
        echo "<script>
            alert(\'Error: " . $e->getMessage() . "\');
            window.history.back();
        </script>";
    }
}
?>';

    // DELETE PHP
    $delete_php = '<?php
// delete.php - Delete Record
require "db.php";

if (isset($_GET["' . $pk . '"])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM ' . $table . ' WHERE ' . $pk . ' = ?");
        $stmt->execute([$_GET["' . $pk . '"]]);
        
        echo "<script>
            alert(\'Record deleted successfully!\');
            window.location.href = \'index.php\';
        </script>";
    } catch (PDOException $e) {
        echo "<script>
            alert(\'Error: " . $e->getMessage() . "\');
            window.location.href = \'index.php\';
        </script>";
    }
} else {
    echo "<script>
        alert(\'Invalid request!\');
        window.location.href = \'index.php\';
    </script>";
}
?>';

    // LIST PHP
    $list_php = '<?php
// list.php - Display All Records
require "db.php";
$table = \'' . $table . '\';

try {
    $stmt = $pdo->query("SELECT * FROM ' . $table . ' ORDER BY ' . $pk . ' DESC");
    $rows = $stmt->fetchAll();
    
    if (count($rows) > 0) {
        echo "<div class=\"table-responsive\">";
        echo "<table class=\"table table-striped table-hover\">";
        echo "<thead class=\"table-dark\">";
        echo "<tr>";
        foreach (array_keys($rows[0]) as $col) {
            echo "<th>" . ucfirst(str_replace("_", " ", $col)) . "</th>";
        }
        echo "<th>Actions</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        
        foreach ($rows as $row) {
            echo "<tr>";
            foreach ($row as $v) {
                echo "<td>" . htmlspecialchars($v) . "</td>";
            }
            echo "<td>";
            echo "<a href=\"edit.php?' . $pk . '=" . $row["' . $pk . '"] . "\" class=\"btn btn-sm btn-warning me-1\">";
            echo "<i class=\"fas fa-edit\"></i> Edit</a>";
            echo "<a href=\"delete.php?' . $pk . '=" . $row["' . $pk . '"] . "\" class=\"btn btn-sm btn-danger\" ";
            echo "onclick=\"return confirm(\'Are you sure you want to delete this record?\')\">";
            echo "<i class=\"fas fa-trash\"></i> Delete</a>";
            echo "</td>";
            echo "</tr>";
        }
        
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
    } else {
        echo "<div class=\"alert alert-info text-center\">";
        echo "<i class=\"fas fa-info-circle\"></i> No records found in " . ucfirst($table) . " table.";
        echo "</div>";
    }
} catch (PDOException $e) {
    echo "<div class=\"alert alert-danger\">";
    echo "Error: " . $e->getMessage();
    echo "</div>";
}
?>';

    // EDIT FORM PHP
    $edit_form_fields = [];
    foreach ($field_names as $f) {
        if ($f != $pk) {
            $label = ucfirst(str_replace("_", " ", $f));
            $edit_form_fields[] = '        echo "<div class=\"mb-3\">";
        echo "<label for=\"' . $f . '\" class=\"form-label\">' . $label . '</label>";
        echo "<input type=\"text\" class=\"form-control\" id=\"' . $f . '\" name=\"' . $f . '\" value=\"" . htmlspecialchars($record["' . $f . '"]) . "\" required>";
        echo "</div>";';
        }
    }
    
    $edit_php = '<?php
// edit.php - Edit Record Form
require "db.php";

if (isset($_GET["' . $pk . '"])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM ' . $table . ' WHERE ' . $pk . ' = ?");
        $stmt->execute([$_GET["' . $pk . '"]]);
        $record = $stmt->fetch();
        
        if ($record) {
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Edit ' . ucfirst($table) . '</title>
                <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
                <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
            </head>
            <body>
                <div class="container mt-5">
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="card shadow">
                                <div class="card-header bg-primary text-white">
                                    <h4><i class="fas fa-edit"></i> Edit ' . ucfirst($table) . '</h4>
                                </div>
                                <div class="card-body">
                                    <form method="post" action="update.php">
                                        <input type="hidden" name="' . $pk . '" value="<?php echo $record["' . $pk . '"]; ?>">
                                        <?php
' . implode("\n", $edit_form_fields) . '
                                        ?>
                                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                            <a href="index.php" class="btn btn-secondary me-md-2">
                                                <i class="fas fa-arrow-left"></i> Back
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save"></i> Update
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </body>
            </html>
            <?php
        } else {
            echo "<script>
                alert(\'Record not found!\');
                window.location.href = \'index.php\';
            </script>";
        }
    } catch (PDOException $e) {
        echo "<script>
            alert(\'Error: " . $e->getMessage() . "\');
            window.location.href = \'index.php\';
        </script>";
    }
} else {
    echo "<script>
        alert(\'Invalid request!\');
        window.location.href = \'index.php\';
    </script>";
}
?>';

    // ADD FORM PHP
    $add_form_fields = [];
    foreach ($field_names as $f) {
        if ($f != $pk) {
            $label = ucfirst(str_replace("_", " ", $f));
            $add_form_fields[] = '    echo "<div class=\"mb-3\">";
    echo "<label for=\"' . $f . '\" class=\"form-label\">' . $label . '</label>";
    echo "<input type=\"text\" class=\"form-control\" id=\"' . $f . '\" name=\"' . $f . '\" required>";
    echo "</div>";';
        }
    }
    
    $add_php = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New ' . ucfirst($table) . '</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h4><i class="fas fa-plus"></i> Add New ' . ucfirst($table) . '</h4>
                    </div>
                    <div class="card-body">
                        <form method="post" action="insert.php">
                            <?php
' . implode("\n", $add_form_fields) . '
                            ?>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="index.php" class="btn btn-secondary me-md-2">
                                    <i class="fas fa-arrow-left"></i> Back
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> Add Record
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>';

    // MAIN INDEX PHP
    $index_php = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . ucfirst($table) . ' Management System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }
        .main-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            margin: 20px auto;
            padding: 0;
            overflow: hidden;
        }
        .header-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .content-section {
            padding: 30px;
        }
        .btn-custom {
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-custom:hover {
            transform: translateY(-2px);
        }
        .table-custom {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        .footer-credit {
            background: linear-gradient(90deg, #ff9966 0%, #ff5e62 100%);
            color: #fff;
            text-align: center;
            padding: 18px 0 12px 0;
            font-size: 1.2rem;
            font-weight: bold;
            letter-spacing: 1px;
            border-top-left-radius: 20px;
            border-top-right-radius: 20px;
            box-shadow: 0 -2px 10px rgba(255, 94, 98, 0.1);
            margin-top: 40px;
        }
        .footer-credit i {
            margin-right: 8px;
            color: #fff700;
            animation: star-glow 1.5s infinite alternate;
        }
        @keyframes star-glow {
            from { text-shadow: 0 0 8px #fff700, 0 0 16px #fff700; }
            to { text-shadow: 0 0 16px #fff700, 0 0 32px #fff700; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-container">
            <div class="header-section">
                <h1><i class="fas fa-database"></i> ' . ucfirst($table) . ' Management System</h1>
                <p>Complete CRUD operations for ' . $table . ' table</p>
            </div>
            <div class="content-section">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3>All Records</h3>
                    <a href="add.php" class="btn btn-success btn-custom">
                        <i class="fas fa-plus"></i> Add New Record
                    </a>
                </div>
                <div class="table-custom">
                    <?php include "list.php"; ?>
                </div>
                <div class="text-center mt-4">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i> 
                        Generated by PHP CRUD Generator Pro
                    </small>
                </div>
            </div>
            <div class="footer-credit">
                <i class="fas fa-star"></i> Created By Jigar Oza
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>';

    // Handle different actions
    if ($action === 'view') {
        // Display code in browser
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Generated CRUD Code - <?php echo ucfirst($table); ?></title>
            <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-dark.min.css" rel="stylesheet">
            <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-core.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>
            <style>
                body { background: #f8f9fa; }
                .code-container { background: #2d3748; border-radius: 10px; }
                .nav-pills .nav-link.active { background: #667eea; }
            </style>
        </head>
        <body>
            <div class="container-fluid py-4">
                <div class="text-center mb-4">
                    <h1><i class="fas fa-code"></i> Generated CRUD Code for "<?php echo ucfirst($table); ?>"</h1>
                    <p class="text-muted">Copy the code below to your project files</p>
                </div>
                
                <ul class="nav nav-pills justify-content-center mb-4" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pills-sql-tab" data-bs-toggle="pill" data-bs-target="#pills-sql">SQL</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-db-tab" data-bs-toggle="pill" data-bs-target="#pills-db">db.php</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-index-tab" data-bs-toggle="pill" data-bs-target="#pills-index">index.php</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-insert-tab" data-bs-toggle="pill" data-bs-target="#pills-insert">insert.php</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-update-tab" data-bs-toggle="pill" data-bs-target="#pills-update">update.php</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-delete-tab" data-bs-toggle="pill" data-bs-target="#pills-delete">delete.php</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-list-tab" data-bs-toggle="pill" data-bs-target="#pills-list">list.php</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-edit-tab" data-bs-toggle="pill" data-bs-target="#pills-edit">edit.php</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-add-tab" data-bs-toggle="pill" data-bs-target="#pills-add">add.php</button>
                    </li>
                </ul>
                
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="pills-sql">
                        <div class="code-container p-3">
                            <pre><code class="language-sql"><?php echo htmlspecialchars($sql); ?></code></pre>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pills-db">
                        <div class="code-container p-3">
                            <pre><code class="language-php"><?php echo htmlspecialchars($db_php); ?></code></pre>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pills-index">
                        <div class="code-container p-3">
                            <pre><code class="language-php"><?php echo htmlspecialchars($index_php); ?></code></pre>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pills-insert">
                        <div class="code-container p-3">
                            <pre><code class="language-php"><?php echo htmlspecialchars($insert_php); ?></code></pre>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pills-update">
                        <div class="code-container p-3">
                            <pre><code class="language-php"><?php echo htmlspecialchars($update_php); ?></code></pre>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pills-delete">
                        <div class="code-container p-3">
                            <pre><code class="language-php"><?php echo htmlspecialchars($delete_php); ?></code></pre>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pills-list">
                        <div class="code-container p-3">
                            <pre><code class="language-php"><?php echo htmlspecialchars($list_php); ?></code></pre>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pills-edit">
                        <div class="code-container p-3">
                            <pre><code class="language-php"><?php echo htmlspecialchars($edit_php); ?></code></pre>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pills-add">
                        <div class="code-container p-3">
                            <pre><code class="language-php"><?php echo htmlspecialchars($add_php); ?></code></pre>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <form method="post" style="display: inline;">
                        <?php
                        foreach ($_POST as $key => $value) {
                            if (is_array($value)) {
                                foreach ($value as $subkey => $subvalue) {
                                    if (is_array($subvalue)) {
                                        foreach ($subvalue as $subsubkey => $subsubvalue) {
                                            echo '<input type="hidden" name="' . htmlspecialchars($key) . '[' . htmlspecialchars($subkey) . '][' . htmlspecialchars($subsubkey) . ']" value="' . htmlspecialchars($subsubvalue) . '">';
                                        }
                                    } else {
                                        echo '<input type="hidden" name="' . htmlspecialchars($key) . '[' . htmlspecialchars($subkey) . ']" value="' . htmlspecialchars($subvalue) . '">';
                                    }
                                }
                            } else {
                                if ($key != 'action') {
                                    echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
                                }
                            }
                        }
                        ?>
                        <input type="hidden" name="action" value="download">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-download"></i> Download All Files as ZIP
                        </button>
                    </form>
                </div>
            </div>
            
            <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>
        <?php
        exit;
    } else {
        // Download as ZIP file
        $zip = new ZipArchive();
        $zipname = $table . '_crud_system.zip';
        
        if ($zip->open($zipname, ZipArchive::CREATE) === TRUE) {
            $zip->addFromString('create_table.sql', $sql);
            $zip->addFromString('db.php', $db_php);
            $zip->addFromString('index.php', $index_php);
            $zip->addFromString('insert.php', $insert_php);
            $zip->addFromString('update.php', $update_php);
            $zip->addFromString('delete.php', $delete_php);
            $zip->addFromString('list.php', $list_php);
            $zip->addFromString('edit.php', $edit_php);
            $zip->addFromString('add.php', $add_php);
            
            // Add README file
            $readme = "# " . ucfirst($table) . " CRUD System

Generated by PHP CRUD Generator Pro

## Installation Instructions:

1. Extract all files to your web server directory
2. Import 'create_table.sql' into your MySQL database
3. Edit 'db.php' and update database connection details:
   - Change YOUR_DATABASE_NAME to your actual database name
   - Update username and password if needed
4. Access 'index.php' in your browser

## Files Included:

- index.php : Main dashboard with all records
- add.php : Form to add new records
- edit.php : Form to edit existing records
- insert.php : Process new record insertion
- update.php : Process record updates
- delete.php : Handle record deletion
- list.php : Display all records in table format
- db.php : Database connection configuration
- create_table.sql : SQL to create the table

## Features:

✅ Complete CRUD operations (Create, Read, Update, Delete)
✅ Responsive Bootstrap design
✅ Form validation and error handling
✅ Confirmation dialogs for delete operations
✅ Professional UI with icons and animations
✅ MySQL injection protection using prepared statements

## Support:

This system was generated automatically. 
Make sure to test thoroughly before using in production.

Generated on: " . date('Y-m-d H:i:s') . "
";
            
            $zip->addFromString('README.md', $readme);
            $zip->close();

            header('Content-Type: application/zip');
            header('Content-disposition: attachment; filename=' . $zipname);
            header('Content-Length: ' . filesize($zipname));
            readfile($zipname);
            unlink($zipname);
            exit;
        } else {
            echo "<script>alert('Error creating ZIP file!'); window.history.back();</script>";
        }
    }
}
?>