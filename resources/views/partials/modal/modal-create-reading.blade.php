<!-- Create Water Reading Modal - Slideover -->
<div id="createReadingModal" class="fixed inset-0 z-999999 hidden" style="isolation: isolate;" aria-labelledby="slideover-title" role="dialog" aria-modal="true">
    <!-- Backdrop with fade -->
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

    
    <!-- Slideover Panel - slides in from right -->
    <div class="fixed inset-y-0 right-0 max-w-full flex">
        <div class="fixed top-0 right-0 h-full bg-white dark:bg-gray-900 shadow-2xl overflow-y-auto z-999999" style="width: 38rem; max-width: calc(100% - 2rem);" id="slideoverPanel">
            
            <div class="h-full flex flex-col bg-white dark:bg-gray-900 shadow-xl">
                <!-- Header -->
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-800">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white" id="slideover-title">
                            Water Meter Reading
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            <span id="modeDescription">Record water meter readings for units</span>
                        </p>
                    </div>
                    <button onclick="closeCreateReadingModal()" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <!-- Mode Toggle -->
                <div id="modeToggleContainer" class="p-4 border-b border-gray-200 dark:border-gray-800">
                    <div class="flex rounded-lg bg-gray-100 p-1 dark:bg-gray-800">
                        <button type="button" onclick="setMode('single')" id="modeSingleBtn" class="mode-btn flex-1 rounded-md px-4 py-2 text-sm font-medium transition-all bg-white text-gray-800 shadow-sm dark:bg-gray-700 dark:text-white">
                            <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Single Unit
                        </button>
                        <button type="button" onclick="setMode('bulk')" id="modeBulkBtn" class="mode-btn flex-1 rounded-md px-4 py-2 text-sm font-medium transition-all text-gray-600 dark:text-gray-400">
                            <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            Bulk Reading
                        </button>
                        <button type="button" onclick="setMode('multimonth')" id="modeMultiMonthBtn" class="mode-btn flex-1 rounded-md px-4 py-2 text-sm font-medium transition-all text-gray-600 dark:text-gray-400">
                            <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Multi-Month (Single Unit)
                        </button>
                    </div>
                </div>
                
                <!-- Form Content -->
                <form id="createReadingForm" class="flex-1 flex flex-col overflow-y-auto">
                    @csrf
                    <div id="formContent" class="flex-1 p-4 space-y-4"></div>
                    
                    <!-- Footer with buttons -->
                    <div class="border-t border-gray-200 dark:border-gray-800 p-4">
                        <div class="flex justify-end gap-3">
                            <button type="button" onclick="closeCreateReadingModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                Cancel
                            </button>
                            <button type="submit" id="submitBtn" class="px-4 py-2 text-sm font-medium text-white bg-brand-500 rounded-lg hover:bg-brand-600">
                                Save Reading
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// ============================================================
// COMPLETE FIXED VERSION - WITH ESTATE RATE SUPPORT & DUPLICATE PREVENTION
// ============================================================

// Prevent duplicate declaration errors
if (typeof currentMode === 'undefined') {
    var currentMode = 'bulk';
}
if (typeof allUnits === 'undefined') {
    var allUnits = [];
}
if (typeof allEstates === 'undefined') {
    var allEstates = [];
}
if (typeof bulkReadingsData === 'undefined') {
    var bulkReadingsData = [];
}

// ==================== FETCH DATA ====================
async function fetchData() {
    try {
        console.log('Fetching units...');
        const unitsFromPage = @json($units ?? []);
        
        // Define estate rates manually (since they're not in the data)
        const estateRates = {
            1: 150,  // Danaff Towers
            2: 200,  // Bloomfield Apartments
            // Add more estates as needed
        };
        
        if (unitsFromPage.length > 0) {
            allUnits = unitsFromPage;
            console.log('Units loaded from page data:', allUnits.length);
            
            const estateMap = {};
            allUnits.forEach(u => {
                if (u.estate_id && u.estate_name) {
                    if (!estateMap[u.estate_id]) {
                        estateMap[u.estate_id] = u.estate_name;
                    }
                }
            });
            allEstates = Object.keys(estateMap).map(id => ({
                id: id,
                name: estateMap[id],
                water_rate: estateRates[id] || 50
            }));
            console.log('Estates extracted from units:', allEstates.length);
            
            populateEstateDropdown();
            return;
        }
        
        const response = await fetch('/api/units/with-water-readings');
        if (!response.ok) {
            console.warn('API returned status:', response.status);
            return;
        }
        
        const data = await response.json();
        if (data.success && data.units) {
            allUnits = data.units;
            console.log('Units loaded:', allUnits.length);
        } else if (Array.isArray(data)) {
            allUnits = data;
            console.log('Units loaded (array):', allUnits.length);
        }
        
        const estateMap = {};
        allUnits.forEach(u => {
            if (u.estate_id && u.estate_name) {
                if (!estateMap[u.estate_id]) {
                    estateMap[u.estate_id] = u.estate_name;
                }
            }
        });
        allEstates = Object.keys(estateMap).map(id => ({
            id: id,
            name: estateMap[id],
            water_rate: estateRates[id] || 50
        }));
        console.log('Estates extracted from units:', allEstates.length);
        
        populateEstateDropdown();
        
    } catch (error) {
        console.error('Error fetching data:', error);
        const unitsFromPage = @json($units ?? []);
        if (unitsFromPage.length > 0) {
            allUnits = unitsFromPage;
            console.log('Units loaded from page data (fallback):', allUnits.length);
        }
    }
}

// ==================== POPULATE ESTATE DROPDOWN ====================
function populateEstateDropdown() {
    const select = document.getElementById('bulkEstateSelect');
    if (!select) return;
    
    let html = '<option value="">All Estates</option>';
    allEstates.forEach(estate => {
        const id = typeof estate === 'object' ? estate.id : estate;
        const name = typeof estate === 'object' ? estate.name : estate;
        html += `<option value="${id}">${name}</option>`;
    });
    select.innerHTML = html;
}

// ==================== SET MODE ====================
function setMode(mode) {
    currentMode = mode;
    
    document.querySelectorAll('.mode-btn').forEach(btn => {
        btn.classList.remove('bg-white', 'text-gray-800', 'shadow-sm', 'dark:bg-gray-700', 'dark:text-white');
        btn.classList.add('text-gray-600', 'dark:text-gray-400');
    });
    
    const activeBtn = document.getElementById(`mode${mode.charAt(0).toUpperCase() + mode.slice(1)}Btn`);
    if (activeBtn) {
        activeBtn.classList.remove('text-gray-600', 'dark:text-gray-400');
        activeBtn.classList.add('bg-white', 'text-gray-800', 'shadow-sm', 'dark:bg-gray-700', 'dark:text-white');
    }
    
    renderForm();
}

// ==================== RENDER FORM ====================
function renderForm() {
    const container = document.getElementById('formContent');
    if (!container) return;
    
    if (currentMode === 'single') {
        container.innerHTML = renderSingleMode();
    } else if (currentMode === 'bulk') {
        container.innerHTML = renderBulkMode();
        setTimeout(() => {
            populateEstateDropdown();
            if (allEstates.length > 0) {
                const select = document.getElementById('bulkEstateSelect');
                if (select && !select.value) {
                    select.value = allEstates[0].id;
                }
                loadBulkUnits();
            }
        }, 100);
    } else if (currentMode === 'multimonth') {
        container.innerHTML = renderMultiMonthMode();
    }
}

// ==================== BULK MODE ====================
function renderBulkMode() {
    const today = new Date().toISOString().split('T')[0];
    const currentMonth = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long' });
    
    return `
        <!-- Month Info Banner -->
        <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg border border-blue-200 dark:border-blue-800">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span class="text-sm font-medium text-blue-800 dark:text-blue-300">
                    Recording readings for: <span id="currentMonthDisplay" class="font-bold">${currentMonth}</span>
                </span>
            </div>
            <div class="text-xs text-blue-600 dark:text-blue-400 mt-1 ml-7 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span><strong>One reading per month per unit</strong> - Units already recorded are locked individually</span>
            </div>
        </div>
        
        <!-- Estate Selection -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Select Estate
            </label>
            <select id="bulkEstateSelect" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800" onchange="loadBulkUnits()">
                <option value="">Loading estates...</option>
            </select>
        </div>
        
        <!-- Reading Month and Date -->
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Billing Month
                </label>
                <input type="month" id="bulkReadingMonth" 
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800" 
                       value="{{ date('Y-m') }}" 
                       max="{{ date('Y-m') }}"
                       onchange="updateMonthDisplay(); loadBulkUnits();">
                <p class="text-xs text-gray-400 mt-1">Only current and past months are allowed</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Reading Date
                </label>
                <input type="date" id="bulkReadingDate" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800" value="${today}">
                <p class="text-xs text-gray-400 mt-1">The actual date when readings were taken</p>
            </div>
        </div>
        
        <!-- Notes -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Notes (Optional)
            </label>
            <textarea id="bulkNotes" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800" placeholder="General notes..."></textarea>
        </div>
        
        <!-- Units Table with Stats Inside -->
        <div class="border border-gray-200 rounded-lg overflow-hidden dark:border-gray-700">
            <div class="bg-gray-50 dark:bg-gray-800 px-4 py-2 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <span class="font-medium text-gray-800 dark:text-white/90">Unit Readings</span>
                <div class="flex gap-2">
                    <button type="button" onclick="autoFillBulk()" class="px-3 py-1 text-xs bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 rounded-lg transition-colors">
                        Auto-Fill
                    </button>
                    <button type="button" onclick="clearBulk()" class="px-3 py-1 text-xs bg-red-100 hover:bg-red-200 text-red-700 rounded-lg transition-colors">
                        Clear All
                    </button>
                </div>
            </div>
            
            <div id="bulkStats" class="grid grid-cols-3 gap-3 p-3 bg-blue-50 dark:bg-blue-900/20 border-b border-gray-200 dark:border-gray-700">
                <div class="text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total Units</p>
                    <p class="text-xl font-bold text-gray-800 dark:text-white" id="bulkTotalUnits">0</p>
                </div>
                <div class="text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Readings Entered</p>
                    <p class="text-xl font-bold text-green-600 dark:text-green-400" id="bulkEntered">0</p>
                </div>
                <div class="text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total Consumption</p>
                    <p class="text-xl font-bold text-blue-600 dark:text-blue-400" id="bulkTotalConsumption">0.00 m³</p>
                </div>
            </div>
            
            <div class="overflow-x-auto max-h-96 overflow-y-auto">
                <div id="bulkTableContainer" class="p-4">
                    <div class="text-center text-gray-500 py-8">
                        <p>Loading units...</p>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// ==================== UPDATE MONTH DISPLAY ====================
function updateMonthDisplay() {
    const monthInput = document.getElementById('bulkReadingMonth');
    if (monthInput && monthInput.value) {
        const [year, month] = monthInput.value.split('-');
        const date = new Date(parseInt(year), parseInt(month) - 1);
        const monthName = date.toLocaleDateString('en-US', { year: 'numeric', month: 'long' });
        const display = document.getElementById('currentMonthDisplay');
        if (display) {
            display.textContent = monthName;
        }
    }
}

// ==================== CHECK IF UNIT HAS READING FOR MONTH ====================
function unitHasReadingForMonth(unit, month) {
    if (!unit || !month || !unit.last_reading_date) return false;
    try {
        const readingDate = new Date(unit.last_reading_date);
        const readingMonth = readingDate.getFullYear() + '-' + String(readingDate.getMonth() + 1).padStart(2, '0');
        return readingMonth === month;
    } catch (e) {
        return false;
    }
}

// ==================== LOAD BULK UNITS ====================
async function loadBulkUnits() {
    const estateId = document.getElementById('bulkEstateSelect').value;
    const readingMonth = document.getElementById('bulkReadingMonth').value;
    
    console.log('Loading units for estate:', estateId);
    console.log('Reading month:', readingMonth);
    console.log('Total units available:', allUnits.length);
    
    updateMonthDisplay();
    
    if (allUnits.length === 0) {
        document.getElementById('bulkTableContainer').innerHTML = `
            <div class="text-center text-gray-500 py-8">
                <p>No units loaded. Please refresh the page.</p>
                <button onclick="location.reload()" class="mt-3 px-4 py-2 bg-blue-500 text-white rounded-lg text-sm hover:bg-blue-600">
                    Refresh Page
                </button>
            </div>
        `;
        return;
    }
    
    let filteredUnits = allUnits;
    if (estateId) {
        filteredUnits = allUnits.filter(u => String(u.estate_id) === String(estateId));
        console.log('Filtered units:', filteredUnits.length);
    }
    
    if (filteredUnits.length === 0) {
        document.getElementById('bulkTableContainer').innerHTML = `
            <div class="text-center text-gray-500 py-8">
                <p>No units found for this estate.</p>
                <p class="text-xs text-gray-400 mt-2">Total units: ${allUnits.length} | Estate ID: ${estateId || 'All'}</p>
            </div>
        `;
        updateBulkStats();
        return;
    }
    
    // ========== FETCH EXISTING READINGS FOR THIS MONTH ==========
    let existingReadings = {};
    if (readingMonth) {
        try {
            const unitIds = filteredUnits.map(u => u.id).join(',');
            const response = await fetch(`/water/api/water/readings/bulk?unit_ids=${unitIds}&start_month=${readingMonth}&end_month=${readingMonth}`);
            const data = await response.json();
            if (data.success && data.readings) {
                data.readings.forEach(reading => {
                    existingReadings[reading.unit_id] = reading;
                });
                console.log('Existing readings found:', Object.keys(existingReadings).length);
            }
        } catch (error) {
            console.warn('Could not fetch existing readings, using unit data:', error);
        }
    }
    
    const monthDisplay = readingMonth ? new Date(readingMonth + '-01').toLocaleDateString('en-US', { year: 'numeric', month: 'long' }) : 'Current Month';
    let hasReadingCount = Object.keys(existingReadings).length;
    
    let infoMessage = `
        <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg border border-blue-200 dark:border-blue-800 mb-3">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm font-medium text-blue-800 dark:text-blue-300">
                    ${hasReadingCount} of ${filteredUnits.length} units already have readings for ${monthDisplay}
                </span>
            </div>
            <p class="text-xs text-blue-600 dark:text-blue-400 mt-1 ml-7">
                Units with existing readings are <strong>locked</strong> and cannot be edited. 
                You can still enter readings for units without readings.
            </p>
        </div>
    `;
    
    let html = `
        <div class="text-xs text-gray-400 mb-2">Showing ${filteredUnits.length} units for ${monthDisplay}</div>
        ${infoMessage}
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800 sticky top-0">
                <tr>
                    <th class="px-3 py-2 text-left">Unit</th>
                    <th class="px-3 py-2 text-center">Previous Reading</th>
                    <th class="px-3 py-2 text-center">Current Reading</th>
                    <th class="px-3 py-2 text-center">Consumption</th>
                    <th class="px-3 py-2 text-right">Amount</th>
                    <th class="px-3 py-2 text-center">Status</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    bulkReadingsData = [];
    
    for (let index = 0; index < filteredUnits.length; index++) {
        const unit = filteredUnits[index];
        
        // Check if this unit already has a reading for this month
        const existingReading = existingReadings[unit.id];
        const hasExistingReading = !!existingReading;
        
        const prevReading = parseFloat(unit.current_water_reading) || parseFloat(unit.previous_water_reading) || 0;
        
        // Get the estate rate from allEstates
        let estateRate = 50;
        if (unit.estate_id) {
            const estate = allEstates.find(e => String(e.id) === String(unit.estate_id));
            if (estate && estate.water_rate) {
                estateRate = parseFloat(estate.water_rate);
            }
        }
        const rate = parseFloat(unit.custom_water_rate) || estateRate;
        
        const billingType = unit.water_billing_type || 'consumption';
        const flatRate = parseFloat(unit.water_charge) || 0;
        
        // If reading exists, pre-populate the values
        const existingValue = hasExistingReading ? existingReading.current_reading : 0;
        const existingConsumption = hasExistingReading ? existingReading.consumption : 0;
        const existingCharge = hasExistingReading ? existingReading.charge : 0;
        const existingId = hasExistingReading ? existingReading.id : null;
        
        const readingObj = {
            unitId: unit.id,
            unitNumber: unit.unit_number,
            estateName: unit.estate_name,
            prevReading: prevReading,
            rate: rate,
            billingType: billingType,
            flatRate: flatRate,
            currentReading: existingValue,
            consumption: existingConsumption,
            amount: existingCharge,
            hasReading: hasExistingReading,
            exists: hasExistingReading,
            readingId: existingId
        };
        bulkReadingsData.push(readingObj);
        
        const prevDate = unit.last_reading_date ? new Date(unit.last_reading_date).toLocaleDateString() : 'No previous';
        
        // Determine unit status
        let unitStatus = 'Pending';
        let unitStatusClass = 'bg-gray-200 text-gray-700';
        let inputDisabled = '';
        let inputClass = '';
        let rowClass = '';
        let displayValue = existingValue > 0 ? existingValue : '';
        
        if (hasExistingReading) {
            unitStatus = '✅ Already Recorded';
            unitStatusClass = 'bg-green-100 text-green-700';
            inputDisabled = 'disabled';
            inputClass = 'border-green-400 bg-green-50 dark:bg-green-900/30 opacity-70';
            rowClass = 'bg-green-50 dark:bg-green-900/10';
        }
        
        html += `
            <tr class="border-t border-gray-200 dark:border-gray-700 ${rowClass}" data-index="${index}">
                <td class="px-3 py-2">
                    <span class="font-medium">${unit.unit_number}</span>
                    <span class="text-xs text-gray-400 block">${unit.estate_name}</span>
                    ${hasExistingReading ? '<span class="text-xs text-green-500 block">✓ Already recorded</span>' : ''}
                </td>
                <td class="px-3 py-2 text-center">
                    <div>
                        <span class="font-medium">${prevReading.toFixed(2)}</span>
                        <span class="text-xs text-gray-400 block">${prevDate}</span>
                    </div>
                </td>
                <td class="px-3 py-2">
                    <input type="number" 
                           step="0.01" 
                           data-index="${index}"
                           value="${displayValue}"
                           oninput="updateBulkReading(this)"
                           class="w-full max-w-[120px] mx-auto block px-2 py-1 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:border-gray-600 dark:bg-gray-800 ${inputClass}"
                           placeholder="${hasExistingReading ? 'Locked' : 'Enter'}"
                           ${inputDisabled}>
                    ${hasExistingReading ? '<span class="text-xs text-green-500 block text-center">Existing: ' + existingValue.toFixed(2) + '</span>' : ''}
                </td>
                <td class="px-3 py-2 text-center consumption-display">${hasExistingReading && existingConsumption > 0 ? existingConsumption.toFixed(2) + ' m³' : '-'}</td>
                <td class="px-3 py-2 text-right amount-display">${hasExistingReading && existingCharge > 0 ? 'KES ' + existingCharge.toFixed(2) : '-'}</td>
                <td class="px-3 py-2 text-center">
                    <span class="status-badge px-2 py-0.5 rounded-full text-xs font-medium ${unitStatusClass}">
                        ${unitStatus}
                    </span>
                </td>
            </tr>
        `;
    }
    
    html += `</tbody></table>`;
    document.getElementById('bulkTableContainer').innerHTML = html;
    updateBulkStats();
    
    // Always enable submit button - user should be able to save new readings
    const submitBtn = document.getElementById('submitBtn');
    if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Save Reading';
        submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    }
}

// ==================== UPDATE BULK READING ====================
function updateBulkReading(input) {
    const index = parseInt(input.dataset.index);
    const value = parseFloat(input.value) || 0;
    
    if (isNaN(index) || index < 0 || index >= bulkReadingsData.length) {
        console.error('Invalid index:', index);
        return;
    }
    
    const data = bulkReadingsData[index];
    if (!data) return;
    
    // Check if this unit already has a reading
    if (data.exists) {
        input.value = '';
        showToast('This unit already has a reading for this month.', 'warning');
        return;
    }
    
    data.currentReading = value;
    const consumption = value - data.prevReading;
    
    if (value > 0 && value < data.prevReading) {
        input.classList.add('border-red-500', 'bg-red-50');
        input.classList.remove('border-green-500', 'bg-green-50');
        data.consumption = 0;
        data.amount = 0;
        data.hasReading = false;
        updateRowDisplay(index, 'error');
        updateBulkStats();
        return;
    }
    
    input.classList.remove('border-red-500', 'bg-red-50');
    
    if (value > 0) {
        input.classList.add('border-green-500', 'bg-green-50');
        data.consumption = consumption > 0 ? consumption : 0;
        data.hasReading = true;
        
        if (data.billingType === 'flat') {
            data.amount = data.flatRate || 0;
        } else {
            data.amount = data.consumption * data.rate;
        }
        
        updateRowDisplay(index, 'normal');
    } else {
        input.classList.remove('border-green-500', 'bg-green-50');
        data.consumption = 0;
        data.amount = 0;
        data.hasReading = false;
        updateRowDisplay(index, 'pending');
    }
    
    updateBulkStats();
}

// ==================== UPDATE ROW DISPLAY ====================
function updateRowDisplay(index, status) {
    const row = document.querySelector(`tr[data-index="${index}"]`);
    if (!row) return;
    
    const data = bulkReadingsData[index];
    if (!data) return;
    
    const consumptionCell = row.querySelector('.consumption-display');
    const amountCell = row.querySelector('.amount-display');
    const statusBadge = row.querySelector('.status-badge');
    
    if (status === 'error') {
        if (consumptionCell) consumptionCell.textContent = '-';
        if (amountCell) amountCell.textContent = '-';
        if (statusBadge) {
            statusBadge.className = 'status-badge px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700';
            statusBadge.textContent = '⚠️ Below Previous';
        }
        return;
    }
    
    if (data.hasReading && data.consumption > 0) {
        if (consumptionCell) consumptionCell.textContent = data.consumption.toFixed(2) + ' m³';
        if (amountCell) amountCell.textContent = 'KES ' + data.amount.toFixed(2);
        if (statusBadge) {
            statusBadge.className = 'status-badge px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700';
            statusBadge.textContent = '✅ Recorded';
        }
    } else if (data.hasReading && data.consumption === 0) {
        if (consumptionCell) consumptionCell.textContent = '0.00 m³';
        if (amountCell) amountCell.textContent = data.billingType === 'flat' ? 'KES ' + data.amount.toFixed(2) : 'KES 0.00';
        if (statusBadge) {
            statusBadge.className = 'status-badge px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700';
            statusBadge.textContent = 'Zero Usage';
        }
    } else {
        if (consumptionCell) consumptionCell.textContent = '-';
        if (amountCell) amountCell.textContent = '-';
        if (statusBadge) {
            statusBadge.className = 'status-badge px-2 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-700';
            statusBadge.textContent = 'Pending';
        }
    }
}

// ==================== UPDATE STATS ====================
function updateBulkStats() {
    const total = bulkReadingsData.length;
    const entered = bulkReadingsData.filter(d => d.hasReading).length;
    const totalConsumption = bulkReadingsData.reduce((sum, d) => sum + (d.consumption || 0), 0);
    
    const totalEl = document.getElementById('bulkTotalUnits');
    const enteredEl = document.getElementById('bulkEntered');
    const consumptionEl = document.getElementById('bulkTotalConsumption');
    
    if (totalEl) totalEl.textContent = total;
    if (enteredEl) enteredEl.textContent = entered;
    if (consumptionEl) consumptionEl.textContent = totalConsumption.toFixed(2) + ' m³';
}

// ==================== AUTO-FILL ====================
function autoFillBulk() {
    if (bulkReadingsData.length === 0) {
        alert('No units loaded. Please select an estate first.');
        return;
    }
    
    // Check if there are any units without readings
    const availableUnits = bulkReadingsData.filter(d => !d.exists && !d.hasReading);
    if (availableUnits.length === 0) {
        alert('All units already have readings for this month.');
        return;
    }
    
    // Confirm with user
    if (!confirm(`Auto-fill ${availableUnits.length} unit(s) with suggested readings?`)) {
        return;
    }
    
    let filled = 0;
    bulkReadingsData.forEach((data, index) => {
        // Skip if already has a reading
        if (data.exists) return;
        
        if (!data.hasReading) {
            const suggested = data.prevReading > 0 ? data.prevReading + 1 : 1;
            const input = document.querySelector(`input[data-index="${index}"]`);
            if (input) {
                input.value = suggested;
                updateBulkReading(input);
                filled++;
            }
        }
    });
    
    if (filled > 0) {
        showToast(`Auto-filled ${filled} unit(s) with suggested readings. Please verify and save.`, 'success');
    } else {
        alert('All units already have readings or are locked.');
    }
}

// ==================== CLEAR BULK ====================
function clearBulk() {
    if (bulkReadingsData.length === 0) return;
    if (!confirm('Clear all pending readings? This will not affect already recorded readings.')) return;
    
    bulkReadingsData.forEach((data, index) => {
        // Skip if already has a reading
        if (data.exists) return;
        
        data.currentReading = 0;
        data.consumption = 0;
        data.amount = 0;
        data.hasReading = false;
        const input = document.querySelector(`input[data-index="${index}"]`);
        if (input) {
            input.value = '';
            input.classList.remove('border-green-500', 'bg-green-50', 'border-red-500', 'bg-red-50');
        }
        updateRowDisplay(index, 'pending');
    });
    
    updateBulkStats();
    showToast('All pending readings cleared.', 'info');
}

// ==================== SINGLE MODE ====================
function renderSingleMode() {
    let unitOptions = '<option value="">Select Unit</option>';
    allUnits.forEach(unit => {
        unitOptions += `<option value="${unit.id}">${unit.unit_number} - ${unit.estate_name}</option>`;
    });
    
    return `
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Select Unit</label>
            <select id="unit_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800">
                ${unitOptions}
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Current Reading (m³)</label>
            <input type="number" id="current_reading" step="0.01" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reading Date</label>
            <input type="date" id="reading_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800" value="{{ date('Y-m-d') }}">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
            <textarea id="notes" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800"></textarea>
        </div>
    `;
}

// ==================== MULTI-MONTH MODE ====================
function renderMultiMonthMode() {
    let unitOptions = '<option value="">Select Unit</option>';
    allUnits.forEach(unit => {
        unitOptions += `<option value="${unit.id}">${unit.unit_number} - ${unit.estate_name}</option>`;
    });
    
    return `
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Select Unit</label>
            <select id="mm_unit_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800">
                ${unitOptions}
            </select>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Month</label>
                <input type="month" id="start_month" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Month</label>
                <input type="month" id="end_month" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Starting Reading (m³)</label>
            <input type="number" id="starting_reading" step="0.01" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Distribution Method</label>
            <select id="distribution_method" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800">
                <option value="equal">Equal Distribution</option>
                <option value="increasing">Increasing</option>
                <option value="decreasing">Decreasing</option>
                <option value="manual">Manual Entry</option>
            </select>
        </div>
        <div class="border rounded-lg overflow-hidden">
            <div class="overflow-x-auto max-h-60 overflow-y-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <th class="px-3 py-2 text-left">Month</th>
                            <th class="px-3 py-2 text-left">Reading</th>
                            <th class="px-3 py-2 text-left">Consumption</th>
                            <th class="px-3 py-2 text-left">Charge</th>
                        </tr>
                    </thead>
                    <tbody id="mmTableBody"></tbody>
                </table>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
            <textarea id="mm_notes" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800"></textarea>
        </div>
    `;
}

// ==================== MODAL OPEN/CLOSE ====================
function openCreateReadingModal(unitId = null) {
    const modal = document.getElementById('createReadingModal');
    const panel = document.getElementById('slideoverPanel');
    
    if (modal && panel) {
        setMode('bulk');
        modal.classList.remove('hidden');
        setTimeout(() => {
            panel.classList.remove('translate-x-full');
            panel.classList.add('translate-x-0');
        }, 10);
        document.body.style.overflow = 'hidden';
        
        if (allUnits.length === 0) {
            fetchData();
        } else {
            setTimeout(() => loadBulkUnits(), 300);
        }
    }
}

function closeCreateReadingModal() {
    const modal = document.getElementById('createReadingModal');
    const panel = document.getElementById('slideoverPanel');
    
    if (modal && panel) {
        panel.classList.remove('translate-x-0');
        panel.classList.add('translate-x-full');
        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }, 300);
    }
}

// ==================== TOAST NOTIFICATION ====================
function showToast(message, type = 'info') {
    const existingToast = document.querySelector('.toast-notification');
    if (existingToast) existingToast.remove();
    
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500';
    toast.className = `toast-notification fixed bottom-4 right-4 z-[99999] px-4 py-2 rounded-lg shadow-lg text-white flex items-center gap-2 ${bgColor}`;
    toast.innerHTML = `
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            ${type === 'success' 
                ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>'
                : type === 'warning'
                ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.342 16.5c-.77.833.192 2.5 1.732 2.5z"></path>'
                : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>'}
        </svg>
        ${message}
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// ==================== FORM SUBMISSION ====================
document.getElementById('createReadingForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerText = 'Saving...';
    
    try {
        if (currentMode === 'bulk') {
            const readings = bulkReadingsData.filter(d => d.hasReading);
            
            if (readings.length === 0) {
                alert('No readings entered. Please enter at least one reading.');
                submitBtn.disabled = false;
                submitBtn.innerText = 'Save Reading';
                return;
            }
            
            const readingMonth = document.getElementById('bulkReadingMonth').value;
            const readingDate = document.getElementById('bulkReadingDate').value;
            const notes = document.getElementById('bulkNotes').value || '';
            
            const bulkData = readings.map(d => ({
                unit_id: d.unitId,
                current_reading: d.currentReading,
                reading_date: readingDate || readingMonth + '-01',
                notes: notes
            }));
            
            console.log('Saving readings:', bulkData);
            
            const response = await fetch('/water/readings/bulk-matrix', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ readings: bulkData, notes: notes })
            });
            
            const data = await response.json();
            console.log('Save response:', data);
            
            if (data.success) {
                const created = data.results?.success?.length || 0;
                const updated = data.results?.updated?.length || 0;
                const skipped = data.results?.skipped?.length || 0;
                const failed = data.results?.failed?.length || 0;
                
                let message = '';
                if (created > 0) message += `${created} reading(s) created. `;
                if (updated > 0) message += `${updated} reading(s) updated. `;
                if (skipped > 0) {
                    message += `${skipped} reading(s) skipped (already exist). `;
                }
                if (failed > 0) {
                    message += `${failed} reading(s) failed. `;
                }
                
                alert(message || 'Readings processed successfully!');
                
                if (created > 0 || updated > 0) {
                    setTimeout(() => window.location.reload(), 1000);
                }
            } else {
                if (data.errors) {
                    const errorMessages = Object.values(data.errors).flat().join('\n');
                    alert('Validation Error:\n' + errorMessages);
                } else {
                    alert('Error: ' + (data.message || 'Failed to save readings'));
                }
            }
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerText = 'Save Reading';
    }
});

// Close modal on backdrop click
document.getElementById('createReadingModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeCreateReadingModal();
    }
});

// ==================== INITIALIZE ====================
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM ready, fetching data...');
    fetchData();
    setMode('bulk');
});
</script>