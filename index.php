<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP CRUD Generator Pro</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .content {
            padding: 40px;
        }

        .form-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 1.1rem;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e8ed;
            border-radius: 10px;
            font-size: 1rem;
            color: #333;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .table-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin: 20px 0;
        }

        .table-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            font-weight: 600;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e1e8ed;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-success {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(86, 171, 47, 0.3);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(255, 65, 108, 0.3);
        }

        .btn-icon {
            margin-right: 8px;
        }

        .action-buttons {
            text-align: center;
            margin-top: 30px;
        }

        .field-input {
            width: 100%;
            padding: 10px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .field-input:focus {
            border-color: #667eea;
            outline: none;
        }

        .select-field {
            width: 100%;
            padding: 10px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            background: white;
        }

        .radio-field {
            transform: scale(1.2);
            margin: 0 auto;
            display: block;
        }

        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .preview-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            display: none;
        }

        .code-preview {
            background: #2d3748;
            color: #e2e8f0;
            padding: 20px;
            border-radius: 10px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            max-height: 400px;
            overflow-y: auto;
        }

        .tabs {
            display: flex;
            background: #f8f9fa;
            border-radius: 10px 10px 0 0;
            overflow: hidden;
        }

        .tab {
            padding: 15px 25px;
            background: #e9ecef;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .tab.active {
            background: #667eea;
            color: white;
        }

        .tab-content {
            display: none;
            padding: 20px;
            background: white;
            border-radius: 0 0 10px 10px;
        }

        .tab-content.active {
            display: block;
        }

        .download-options {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .container {
                margin: 10px;
                border-radius: 15px;
            }

            .content {
                padding: 20px;
            }

            .header h1 {
                font-size: 2rem;
            }

            .download-options {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-code"></i> PHP CRUD Generator Pro</h1>
            <p>Generate complete CRUD operations with beautiful interface in seconds!</p>
        </div>

        <div class="content">
            <form id="crudForm" method="post" action="generate.php">
                <div class="form-section">
                    <div class="form-group">
                        <label for="table_name">
                            <i class="fas fa-table"></i> Table Name
                        </label>
                        <input type="text" id="table_name" name="table_name" class="form-control" 
                               placeholder="Enter table name (e.g., users, products)" required>
                    </div>
                </div>

                <div class="table-container">
                    <div class="table-header">
                        <i class="fas fa-columns"></i> Define Table Fields
                    </div>
                    <table id="fields">
                        <thead>
                            <tr>
                                <th>Field Name</th>
                                <th>Data Type</th>
                                <th>Length</th>
                                <th>Primary Key</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input type="text" name="fields[1][name]" class="field-input" placeholder="Field name" required></td>
                                <td>
                                    <select name="fields[1][type]" class="select-field">
                                        <option value="INT">INT</option>
                                        <option value="VARCHAR">VARCHAR</option>
                                        <option value="TEXT">TEXT</option>
                                        <option value="BOOL">BOOLEAN</option>
                                        <option value="DATE">DATE</option>
                                        <option value="DATETIME">DATETIME</option>
                                        <option value="DECIMAL">DECIMAL</option>
                                    </select>
                                </td>
                                <td><input type="text" name="fields[1][length]" class="field-input" placeholder="e.g. 255"></td>
                                <td><input type="radio" name="primary_key" value="1" class="radio-field"></td>
                                <td><button type="button" class="btn btn-danger" onclick="removeField(this)">
                                    <i class="fas fa-trash"></i>
                                </button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="action-buttons">
                    <button type="button" class="btn btn-success" onclick="addField()">
                        <i class="fas fa-plus btn-icon"></i>Add Field
                    </button>
                    <button type="button" class="btn btn-primary" onclick="previewCode()">
                        <i class="fas fa-eye btn-icon"></i>Preview Code
                    </button>
                    <button type="button" class="btn btn-primary" onclick="generateCRUD('download')">
                        <i class="fas fa-download btn-icon"></i>Download ZIP
                    </button>
                    <button type="button" class="btn btn-primary" onclick="generateCRUD('view')">
                        <i class="fas fa-code btn-icon"></i>View Code
                    </button>
                </div>

                <div class="loading" id="loading">
                    <div class="spinner"></div>
                    <p>Generating your CRUD files...</p>
                </div>
            </form>

            <div class="preview-section" id="previewSection">
                <div class="tabs">
                    <button class="tab active" onclick="showTab('sql')">SQL</button>
                    <button class="tab" onclick="showTab('insert')">Insert</button>
                    <button class="tab" onclick="showTab('update')">Update</button>
                    <button class="tab" onclick="showTab('delete')">Delete</button>
                    <button class="tab" onclick="showTab('list')">List</button>
                </div>
                <div class="tab-content active" id="sql-content">
                    <div class="code-preview" id="sql-preview"></div>
                </div>
                <div class="tab-content" id="insert-content">
                    <div class="code-preview" id="insert-preview"></div>
                </div>
                <div class="tab-content" id="update-content">
                    <div class="code-preview" id="update-preview"></div>
                </div>
                <div class="tab-content" id="delete-content">
                    <div class="code-preview" id="delete-preview"></div>
                </div>
                <div class="tab-content" id="list-content">
                    <div class="code-preview" id="list-preview"></div>
                </div>
                <div class="download-options">
                    <button class="btn btn-success" onclick="generateCRUD('download')">
                        <i class="fas fa-download"></i> Download All Files
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let fieldCount = 1;

        function addField() {
            fieldCount++;
            let table = document.getElementById('fields').getElementsByTagName('tbody')[0];
            let row = table.insertRow();
            row.innerHTML = `
                <td><input type="text" name="fields[${fieldCount}][name]" class="field-input" placeholder="Field name" required></td>
                <td>
                    <select name="fields[${fieldCount}][type]" class="select-field">
                        <option value="INT">INT</option>
                        <option value="VARCHAR">VARCHAR</option>
                        <option value="TEXT">TEXT</option>
                        <option value="BOOL">BOOLEAN</option>
                        <option value="DATE">DATE</option>
                        <option value="DATETIME">DATETIME</option>
                        <option value="DECIMAL">DECIMAL</option>
                    </select>
                </td>
                <td><input type="text" name="fields[${fieldCount}][length]" class="field-input" placeholder="e.g. 255"></td>
                <td><input type="radio" name="primary_key" value="${fieldCount}" class="radio-field"></td>
                <td><button type="button" class="btn btn-danger" onclick="removeField(this)">
                    <i class="fas fa-trash"></i>
                </button></td>
            `;
            
            swal("Success!", "New field added successfully!", "success");
        }

        function removeField(btn) {
            if (document.querySelectorAll('#fields tbody tr').length > 1) {
                let row = btn.parentNode.parentNode;
                row.parentNode.removeChild(row);
                swal("Deleted!", "Field removed successfully!", "success");
            } else {
                swal("Warning!", "You must have at least one field!", "warning");
            }
        }

        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById(tabName + '-content').classList.add('active');
            
            // Add active class to clicked tab
            event.target.classList.add('active');
        }

        function previewCode() {
            const formData = new FormData(document.getElementById('crudForm'));
            const tableName = formData.get('table_name');
            
            if (!tableName) {
                swal("Error!", "Please enter a table name!", "error");
                return;
            }

            if (!formData.get('primary_key')) {
                swal("Error!", "Please select a primary key field!", "error");
                return;
            }

            // Generate preview code (simplified version)
            const fields = [];
            for (let i = 1; i <= fieldCount; i++) {
                const name = formData.get(`fields[${i}][name]`);
                const type = formData.get(`fields[${i}][type]`);
                const length = formData.get(`fields[${i}][length]`);
                if (name) {
                    fields.push({ name, type, length });
                }
            }

            // Generate SQL preview
            let sqlPreview = `CREATE TABLE \`${tableName}\` (\n`;
            fields.forEach((field, index) => {
                let fieldDef = `    \`${field.name}\` ${field.type}`;
                if (field.type === 'VARCHAR' && field.length) {
                    fieldDef += `(${field.length})`;
                }
                if (formData.get('primary_key') == (index + 1)) {
                    fieldDef += ' PRIMARY KEY AUTO_INCREMENT';
                }
                sqlPreview += fieldDef + (index < fields.length - 1 ? ',' : '') + '\n';
            });
            sqlPreview += ');';

            document.getElementById('sql-preview').textContent = sqlPreview;
            document.getElementById('insert-preview').textContent = `// Insert operation for ${tableName}\n// Complete PHP code will be generated...`;
            document.getElementById('update-preview').textContent = `// Update operation for ${tableName}\n// Complete PHP code will be generated...`;
            document.getElementById('delete-preview').textContent = `// Delete operation for ${tableName}\n// Complete PHP code will be generated...`;
            document.getElementById('list-preview').textContent = `// List operation for ${tableName}\n// Complete PHP code will be generated...`;

            document.getElementById('previewSection').style.display = 'block';
            document.getElementById('previewSection').scrollIntoView({ behavior: 'smooth' });
            
            swal("Success!", "Code preview generated successfully!", "success");
        }

        function generateCRUD(action) {
            const formData = new FormData(document.getElementById('crudForm'));
            const tableName = formData.get('table_name');
            
            if (!tableName) {
                swal("Error!", "Please enter a table name!", "error");
                return;
            }

            if (!formData.get('primary_key')) {
                swal("Error!", "Please select a primary key field!", "error");
                return;
            }

            document.getElementById('loading').style.display = 'block';

            if (action === 'download') {
                // Create a form and submit for download
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'generate.php';
                form.style.display = 'none';
                
                // Add all form data
                const formData = new FormData(document.getElementById('crudForm'));
                for (let [key, value] of formData.entries()) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = value;
                    form.appendChild(input);
                }
                
                // Add action type
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'download';
                form.appendChild(actionInput);
                
                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
                
                setTimeout(() => {
                    document.getElementById('loading').style.display = 'none';
                    swal("Success!", "Your CRUD files are being downloaded!", "success");
                }, 2000);
            } else if (action === 'view') {
                // Open in new window to view code
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'generate.php';
                form.target = '_blank';
                form.style.display = 'none';
                
                // Add all form data
                const formData = new FormData(document.getElementById('crudForm'));
                for (let [key, value] of formData.entries()) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = value;
                    form.appendChild(input);
                }
                
                // Add action type
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'view';
                form.appendChild(actionInput);
                
                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
                
                setTimeout(() => {
                    document.getElementById('loading').style.display = 'none';
                    swal("Success!", "Code opened in new tab!", "success");
                }, 1000);
            }
        }

        // Add some interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            // Add hover effects to form controls
            const formControls = document.querySelectorAll('.form-control, .field-input, .select-field');
            formControls.forEach(control => {
                control.addEventListener('focus', function() {
                    this.style.transform = 'scale(1.02)';
                });
                control.addEventListener('blur', function() {
                    this.style.transform = 'scale(1)';
                });
            });
        });
    </script>
</body>
</html>