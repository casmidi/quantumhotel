<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Management - Improved</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Header Section */
        .header {
            margin-bottom: 40px;
            animation: slideDown 0.5s ease-out;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .header h1 {
            font-size: 32px;
            font-weight: 700;
            color: white;
            margin-bottom: 8px;
        }

        .header p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
        }

        .btn-new {
            background: white;
            color: #667eea;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-new:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        /* Card Container */
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            margin-bottom: 32px;
            overflow: hidden;
            animation: fadeUp 0.6s ease-out;
        }

        /* Form Section */
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 24px;
            color: white;
        }

        .card-header h2 {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
        }

        .card-body {
            padding: 32px;
        }

        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-size: 13px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 12px 14px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s ease;
            background: #f7fafc;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        /* Form Row untuk layout lebih rapi */
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 24px;
            margin-bottom: 24px;
        }

        /* Price Input */
        .price-input-group {
            position: relative;
        }

        .price-input-group::before {
            content: 'Rp';
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            font-weight: 600;
            font-size: 13px;
            margin-top: 23px;
        }

        .price-input-group input {
            padding-left: 35px;
        }

        /* Button Group */
        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 32px;
            padding-top: 32px;
            border-top: 2px solid #e2e8f0;
        }

        .btn {
            padding: 12px 32px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            flex: 1;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #2d3748;
            flex: 1;
        }

        .btn-secondary:hover {
            background: #cbd5e0;
            transform: translateY(-2px);
        }

        /* Search Section */
        .search-section {
            display: flex;
            gap: 16px;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .search-box {
            position: relative;
            flex: 1;
            min-width: 250px;
        }

        .search-box::before {
            content: '🔍';
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
        }

        .search-box input {
            width: 100%;
            padding: 12px 14px 12px 40px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            background: #f7fafc;
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
        }

        .result-count {
            color: #718096;
            font-size: 13px;
            font-weight: 500;
        }

        /* Table Section */
        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f7fafc;
            border-bottom: 2px solid #e2e8f0;
        }

        th {
            padding: 16px 12px;
            text-align: left;
            font-size: 12px;
            font-weight: 700;
            color: #2d3748;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tbody tr {
            border-bottom: 1px solid #e2e8f0;
            transition: background-color 0.2s ease;
        }

        tbody tr:hover {
            background-color: #f7fafc;
        }

        tbody tr.highlight {
            background-color: #edf2f7;
        }

        td {
            padding: 16px 12px;
            font-size: 14px;
            color: #2d3748;
        }

        td.code {
            font-weight: 600;
            color: #667eea;
            font-family: 'Courier New', monospace;
        }

        td.numeric {
            text-align: right;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }

        .btn-icon {
            width: 36px;
            height: 36px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .btn-view {
            background: #bef3f8;
            color: #0891b2;
        }

        .btn-view:hover {
            background: #06b6d4;
            color: white;
            transform: translateY(-2px);
        }

        .btn-edit {
            background: #fef3c7;
            color: #d97706;
        }

        .btn-edit:hover {
            background: #f59e0b;
            color: white;
            transform: translateY(-2px);
        }

        .btn-delete {
            background: #fee2e2;
            color: #dc2626;
        }

        .btn-delete:hover {
            background: #ef4444;
            color: white;
            transform: translateY(-2px);
        }

        /* Status Badge */
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-active {
            background: #dcfce7;
            color: #166534;
        }

        .badge-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Animations */
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #a0aec0;
        }

        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }

        .empty-state p {
            margin-bottom: 24px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 0 16px;
            }

            .header h1 {
                font-size: 24px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .action-buttons {
                flex-direction: column;
            }

            .search-section {
                flex-direction: column;
            }

            .search-box {
                min-width: 100%;
            }

            table {
                font-size: 12px;
            }

            th, td {
                padding: 12px 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-content">
                <div>
                    <h1>📚 Class Management</h1>
                    <p>Manage room class, facilities, and pricing</p>
                </div>
                <button class="btn-new">+ New Class</button>
            </div>
        </div>

        <!-- Form Card -->
        <div class="card">
            <div class="card-header">
                <h2>Add or Edit Class</h2>
            </div>
            <div class="card-body">
                <form>
                    <!-- Grid 1: Class & Facilities -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="class-name">Class Name</label>
                            <input type="text" id="class-name" placeholder="e.g., DELUXE, EXECUTIVE SUITE" value="DELUXE">
                        </div>
                        <div class="form-group">
                            <label for="facilities">Facilities</label>
                            <input type="text" id="facilities" placeholder="e.g., AC, TV, SHOWER" value="AC, TV, SHOWER">
                        </div>
                    </div>

                    <!-- Grid 2: Pricing -->
                    <div class="form-row">
                        <div class="form-group price-input-group">
                            <label for="basic-rate">Basic Rate</label>
                            <input type="number" id="basic-rate" placeholder="Basic room rate" value="500000">
                        </div>
                        <div class="form-group price-input-group">
                            <label for="service">Service Charge</label>
                            <input type="number" id="service" placeholder="Service charge amount" value="500000">
                        </div>
                        <div class="form-group price-input-group">
                            <label for="deposit">Deposit</label>
                            <input type="number" id="deposit" placeholder="Deposit amount" value="550000">
                        </div>
                    </div>

                    <!-- Grid 3: Additional Info -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="capacity">Capacity (Guests)</label>
                            <input type="number" id="capacity" placeholder="Max number of guests" value="2">
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" placeholder="Add detailed description of this room class..."></textarea>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">💾 Save Class</button>
                        <button type="reset" class="btn btn-secondary">↺ Reset Form</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card">
            <div class="card-header">
                <h2>Available Classes</h2>
            </div>
            <div class="card-body">
                <!-- Search -->
                <div class="search-section">
                    <div class="search-box">
                        <input type="text" placeholder="Search by class name or facilities...">
                    </div>
                    <div class="result-count">Total: <strong>13 data</strong></div>
                </div>

                <!-- Table -->
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Facilities</th>
                                <th style="text-align: right;">Rate</th>
                                <th style="text-align: right;">Deposit</th>
                                <th style="text-align: center;">Status</th>
                                <th style="text-align: center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="highlight">
                                <td class="code">DEL001</td>
                                <td><strong>Deluxe</strong></td>
                                <td>AC, TV, Shower</td>
                                <td class="numeric">500,000</td>
                                <td class="numeric">0</td>
                                <td style="text-align: center;"><span class="badge badge-active">Active</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-icon btn-view" title="View">👁️</button>
                                        <button class="btn-icon btn-edit" title="Edit">✎</button>
                                        <button class="btn-icon btn-delete" title="Delete">🗑️</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="code">DLD001</td>
                                <td><strong>Deluxe Double</strong></td>
                                <td>AC, TV</td>
                                <td class="numeric">0</td>
                                <td class="numeric">0</td>
                                <td style="text-align: center;"><span class="badge badge-inactive">Inactive</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-icon btn-view" title="View">👁️</button>
                                        <button class="btn-icon btn-edit" title="Edit">✎</button>
                                        <button class="btn-icon btn-delete" title="Delete">🗑️</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="code">DLF001</td>
                                <td><strong>Deluxe Four</strong></td>
                                <td>AC, TV, Shower</td>
                                <td class="numeric">500,000</td>
                                <td class="numeric">0</td>
                                <td style="text-align: center;"><span class="badge badge-active">Active</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-icon btn-view" title="View">👁️</button>
                                        <button class="btn-icon btn-edit" title="Edit">✎</button>
                                        <button class="btn-icon btn-delete" title="Delete">🗑️</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="code">DLS001</td>
                                <td><strong>Deluxe Suite</strong></td>
                                <td>Bed Room, Living Room, AC, TV Cable</td>
                                <td class="numeric">500,000</td>
                                <td class="numeric">0</td>
                                <td style="text-align: center;"><span class="badge badge-active">Active</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-icon btn-view" title="View">👁️</button>
                                        <button class="btn-icon btn-edit" title="Edit">✎</button>
                                        <button class="btn-icon btn-delete" title="Delete">🗑️</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="code">DLT001</td>
                                <td><strong>Deluxe Triple</strong></td>
                                <td>AC, TV, Shower</td>
                                <td class="numeric">500,000</td>
                                <td class="numeric">0</td>
                                <td style="text-align: center;"><span class="badge badge-active">Active</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-icon btn-view" title="View">👁️</button>
                                        <button class="btn-icon btn-edit" title="Edit">✎</button>
                                        <button class="btn-icon btn-delete" title="Delete">🗑️</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="code">DLW001</td>
                                <td><strong>Deluxe Twin</strong></td>
                                <td>AC, TV</td>
                                <td class="numeric">1,800,000</td>
                                <td class="numeric">0</td>
                                <td style="text-align: center;"><span class="badge badge-active">Active</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-icon btn-view" title="View">👁️</button>
                                        <button class="btn-icon btn-edit" title="Edit">✎</button>
                                        <button class="btn-icon btn-delete" title="Delete">🗑️</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="code">EXE001</td>
                                <td><strong>Executive Suite</strong></td>
                                <td>AC, TV, WIFI</td>
                                <td class="numeric">700,000</td>
                                <td class="numeric">0</td>
                                <td style="text-align: center;"><span class="badge badge-active">Active</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-icon btn-view" title="View">👁️</button>
                                        <button class="btn-icon btn-edit" title="Edit">✎</button>
                                        <button class="btn-icon btn-delete" title="Delete">🗑️</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
