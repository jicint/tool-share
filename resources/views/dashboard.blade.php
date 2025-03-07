<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tool Share - Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group input, .form-group textarea, .form-group select { 
            width: 100%; 
            padding: 8px; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
        }
        .btn { 
            background: #4CAF50; 
            color: white; 
            padding: 10px 15px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
        }
        .btn:hover { background: #45a049; }
        .tool-list { margin-top: 30px; }
        .tool-item { 
            border: 1px solid #ddd; 
            padding: 15px; 
            margin-bottom: 10px; 
            border-radius: 4px; 
        }
        .error { color: red; margin-bottom: 15px; }
        .success { color: green; margin-bottom: 15px; }
        .tabs { margin-bottom: 20px; }
        .tab-btn {
            padding: 10px 20px;
            margin-right: 10px;
            border: none;
            background: #ddd;
            cursor: pointer;
        }
        .tab-btn.active {
            background: #4CAF50;
            color: white;
        }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .status-pending { color: #f39c12; }
        .status-approved { color: #27ae60; }
        .status-rejected { color: #e74c3c; }
        .status-completed { color: #2980b9; }
        .cost-preview {
            margin: 10px 0;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .rental-item {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .rental-actions {
            margin-top: 10px;
        }
        .rental-actions .btn {
            margin-right: 10px;
        }
        .rental-item {
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: box-shadow 0.3s ease;
        }
        .rental-item:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            color: white;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    @if(session('success'))
        <div style="background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background-color: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div style="background-color: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="container">
        <h1>Tool Share Dashboard</h1>
        <p>Welcome, {{ Auth::user()->email }}!</p>

        <!-- Navigation Tabs -->
        <div class="tabs" style="margin-bottom: 20px;">
            <button onclick="showTab('my-tools')" class="tab-btn active">My Tools</button>
            <button onclick="showTab('available-tools')" class="tab-btn">Rent Tools</button>
            <button onclick="showTab('my-rentals')" class="tab-btn">My Rentals</button>
        </div>

        <!-- My Tools Section -->
        <div id="my-tools" class="tab-content">
            <h2>My Tools</h2>
            
            <!-- Add Tool Form -->
            <div class="form-section">
                <h3>Add a New Tool</h3>
                <form action="{{ route('tools.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="name">Tool Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <option value="Power Tools">Power Tools</option>
                            <option value="Hand Tools">Hand Tools</option>
                            <option value="Garden Tools">Garden Tools</option>
                            <option value="Measuring Tools">Measuring Tools</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="condition">Condition</label>
                        <select id="condition" name="condition" required>
                            <option value="New">New</option>
                            <option value="Excellent">Excellent</option>
                            <option value="Good">Good</option>
                            <option value="Fair">Fair</option>
                            <option value="Poor">Poor</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="daily_rate">Daily Rate ($)</label>
                        <input type="number" id="daily_rate" name="daily_rate" step="0.01" min="0" value="{{ old('daily_rate', '0.00') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="3" required>{{ old('description') }}</textarea>
                    </div>

                    <button type="submit" class="btn">Add Tool</button>
                </form>
            </div>

            <!-- List of My Tools -->
            <div class="tool-list">
                @forelse(auth()->user()->tools as $tool)
                    <div class="tool-item">
                        <!-- Tool Display -->
                        <div id="tool-display-{{ $tool->id }}">
                            <h3>{{ $tool->name }}</h3>
                            <p><strong>Category:</strong> {{ $tool->category }}</p>
                            <p><strong>Condition:</strong> {{ $tool->condition }}</p>
                            <p><strong>Daily Rate:</strong> ${{ number_format($tool->daily_rate, 2) }}</p>
                            <p>{{ $tool->description }}</p>
                            <button onclick="showEditForm('{{ $tool->id }}')" class="btn" style="background: #2196F3;">Edit</button>
                        </div>

                        <!-- Edit Form (Hidden by default) -->
                        <div id="edit-form-{{ $tool->id }}" style="display: none;">
                            <h3>Edit Tool</h3>
                            <form action="{{ route('tools.update', $tool->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <div class="form-group">
                                    <label for="edit-name-{{ $tool->id }}">Tool Name</label>
                                    <input type="text" id="edit-name-{{ $tool->id }}" name="name" value="{{ $tool->name }}" required>
                                </div>

                                <div class="form-group">
                                    <label for="edit-category-{{ $tool->id }}">Category</label>
                                    <select id="edit-category-{{ $tool->id }}" name="category" required>
                                        <option value="Power Tools" {{ $tool->category === 'Power Tools' ? 'selected' : '' }}>Power Tools</option>
                                        <option value="Hand Tools" {{ $tool->category === 'Hand Tools' ? 'selected' : '' }}>Hand Tools</option>
                                        <option value="Garden Tools" {{ $tool->category === 'Garden Tools' ? 'selected' : '' }}>Garden Tools</option>
                                        <option value="Measuring Tools" {{ $tool->category === 'Measuring Tools' ? 'selected' : '' }}>Measuring Tools</option>
                                        <option value="Other" {{ $tool->category === 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="edit-condition-{{ $tool->id }}">Condition</label>
                                    <select id="edit-condition-{{ $tool->id }}" name="condition" required>
                                        <option value="New" {{ $tool->condition === 'New' ? 'selected' : '' }}>New</option>
                                        <option value="Excellent" {{ $tool->condition === 'Excellent' ? 'selected' : '' }}>Excellent</option>
                                        <option value="Good" {{ $tool->condition === 'Good' ? 'selected' : '' }}>Good</option>
                                        <option value="Fair" {{ $tool->condition === 'Fair' ? 'selected' : '' }}>Fair</option>
                                        <option value="Poor" {{ $tool->condition === 'Poor' ? 'selected' : '' }}>Poor</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="edit-daily-rate-{{ $tool->id }}">Daily Rate ($)</label>
                                    <input type="number" id="edit-daily-rate-{{ $tool->id }}" name="daily_rate" step="0.01" min="0" value="{{ $tool->daily_rate }}" required>
                                </div>

                                <div class="form-group">
                                    <label for="edit-description-{{ $tool->id }}">Description</label>
                                    <textarea id="edit-description-{{ $tool->id }}" name="description" rows="3" required>{{ $tool->description }}</textarea>
                                </div>

                                <button type="submit" class="btn">Save Changes</button>
                                <button type="button" onclick="hideEditForm('{{ $tool->id }}')" class="btn" style="background: #f44336;">Cancel</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p>You haven't added any tools yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Available Tools for Rent Section -->
        <div id="available-tools" class="tab-content" style="display: none;">
            <h2>Available Tools for Rent</h2>
            
            <!-- Debug info -->
            <div style="background: #f8f9fa; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
                <p><strong>Available Tools:</strong> {{ $tools->where('user_id', '!=', auth()->id())->count() }}</p>
            </div>

            <div class="tool-list">
                @forelse($tools->where('user_id', '!=', auth()->id()) as $tool)
                    <div class="tool-item">
                        <h3>{{ $tool->name }}</h3>
                        <p><strong>Owner:</strong> {{ $tool->user->email }}</p>
                        <p><strong>Category:</strong> {{ $tool->category }}</p>
                        <p><strong>Condition:</strong> {{ $tool->condition }}</p>
                        <p><strong>Daily Rate:</strong> ${{ number_format($tool->daily_rate, 2) }}</p>
                        <p>{{ $tool->description }}</p>
                        
                        <!-- Updated Rental Form with Debug Info -->
                        <div class="rental-form">
                            <h4>Rent This Tool</h4>
                            
                            <!-- Debug info -->
                            <div style="background: #f8f9fa; padding: 10px; margin-bottom: 10px; font-size: 12px;">
                                Form Action: {{ route('rentals.store') }}<br>
                                Tool ID: {{ $tool->id }}<br>
                                Current User: {{ auth()->id() }}
                            </div>

                            <form action="{{ route('rentals.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="tool_id" value="{{ $tool->id }}">
                                
                                <div class="form-group">
                                    <label>Start Date:</label>
                                    <input type="date" 
                                           name="start_date" 
                                           value="{{ date('Y-m-d') }}"
                                           min="{{ date('Y-m-d') }}" 
                                           required>
                                </div>

                                <div class="form-group">
                                    <label>End Date:</label>
                                    <input type="date" 
                                           name="end_date" 
                                           value="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                           min="{{ date('Y-m-d') }}" 
                                           required>
                                </div>

                                <button type="submit" class="btn" 
                                        style="background: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 4px;">
                                    Submit Rental Request
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p>No tools available for rent.</p>
                @endforelse
            </div>
        </div>

        <!-- My Rentals Section -->
        <div id="my-rentals" class="tab-content" style="display: none;">
            <h2>My Rental Requests</h2>
            
            <!-- Debug info -->
            <div style="background: #f8f9fa; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
                <p><strong>Debug Info:</strong></p>
                <p>User ID: {{ auth()->id() }}</p>
                <p>Rentals Count: {{ $rentals->count() }}</p>
                <pre>{{ print_r($rentals->toArray(), true) }}</pre>
            </div>

            <div class="rentals-list">
                @if($rentals && $rentals->count() > 0)
                    @foreach($rentals as $rental)
                        <div class="rental-item" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 4px;">
                            <h3>{{ $rental->tool->name }}</h3>
                            <p><strong>Owner:</strong> {{ $rental->tool->user->email }}</p>
                            <p><strong>Rental Period:</strong> 
                                {{ \Carbon\Carbon::parse($rental->start_date)->format('M d, Y') }} - 
                                {{ \Carbon\Carbon::parse($rental->end_date)->format('M d, Y') }}
                            </p>
                            <p><strong>Total Cost:</strong> ${{ number_format($rental->total_cost, 2) }}</p>
                            <p><strong>Status:</strong> 
                                <span class="status-{{ $rental->status }}" style="
                                    @if($rental->status == 'pending') color: #f39c12;
                                    @elseif($rental->status == 'approved') color: #27ae60;
                                    @elseif($rental->status == 'rejected') color: #e74c3c;
                                    @else color: #2980b9;
                                    @endif
                                ">
                                    {{ ucfirst($rental->status) }}
                                </span>
                            </p>
                        </div>
                    @endforeach
                @else
                    <p>You haven't made any rental requests yet.</p>
                @endif
            </div>

            @if(auth()->user()->tools->count() > 0)
                <h2 style="margin-top: 30px;">Rental Requests for Your Tools</h2>
                <div class="rentals-list">
                    @if($receivedRentals && $receivedRentals->count() > 0)
                        @foreach($receivedRentals as $rental)
                            <div class="rental-item" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 4px;">
                                <h3>{{ $rental->tool->name }}</h3>
                                <p><strong>Requested by:</strong> {{ $rental->user->email }}</p>
                                <p><strong>Rental Period:</strong> 
                                    {{ \Carbon\Carbon::parse($rental->start_date)->format('M d, Y') }} - 
                                    {{ \Carbon\Carbon::parse($rental->end_date)->format('M d, Y') }}
                                </p>
                                <p><strong>Total Cost:</strong> ${{ number_format($rental->total_cost, 2) }}</p>
                                <p><strong>Status:</strong> {{ ucfirst($rental->status) }}</p>
                                
                                @if($rental->status === 'pending')
                                    <div class="rental-actions" style="margin-top: 10px;">
                                        <form action="{{ route('rentals.update', $rental->id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="btn" style="background: #27ae60; color: white; margin-right: 10px;">
                                                Approve
                                            </button>
                                        </form>
                                        
                                        <form action="{{ route('rentals.update', $rental->id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="btn" style="background: #e74c3c; color: white;">
                                                Reject
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <p>No rental requests for your tools yet.</p>
                    @endif
                </div>
            @endif
        </div>

        <form method="POST" action="{{ route('logout') }}" style="margin-top: 20px;">
            @csrf
            <button type="submit" class="btn" style="background: #f44336;">Logout</button>
        </form>
    </div>

    <script>
        function showTab(tabId) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.style.display = 'none';
            });
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            // Show selected tab
            document.getElementById(tabId).style.display = 'block';
            event.target.classList.add('active');
        }

        function calculateCost(toolId, dailyRate) {
            const startDate = document.getElementById(`start_date_${toolId}`).value;
            const endDate = document.getElementById(`end_date_${toolId}`).value;
            
            if (startDate && endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);
                const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
                const totalCost = (days * dailyRate).toFixed(2);
                
                document.getElementById(`cost_preview_${toolId}`).innerHTML = 
                    `Estimated Cost: $${totalCost} (${days} days @ $${dailyRate}/day)`;
            }
        }

        function showEditForm(toolId) {
            document.getElementById('tool-display-' + toolId).style.display = 'none';
            document.getElementById('edit-form-' + toolId).style.display = 'block';
        }

        function hideEditForm(toolId) {
            document.getElementById('tool-display-' + toolId).style.display = 'block';
            document.getElementById('edit-form-' + toolId).style.display = 'none';
        }

        // Show My Tools tab by default
        document.getElementById('my-tools').style.display = 'block';
    </script>
</body>
</html> 