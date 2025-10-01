const HeartBeats = {
    data: {},
    sort: { column: 'status', direction: 'asc' },
    init: function() {
        this.setupSearch();
        this.setupSorting();
        this.renderTable(this.sortData(this.data, this.sort.column, this.sort.direction));
        HeartbeatDetailsModal.init();
    },
    //Function to render table rows
    renderTable: function(data) { 
        const tbody = document.querySelector('#monitoringTable tbody');
        tbody.innerHTML = '';
        const statusArr = {
            'in-progress': "In Progress",
            'completed': "Completed",
            'failed': "Failed"
        };
        
        data.forEach(heartbeat => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${heartbeat.title}</td>
                <td><span class="status-badge status-${heartbeat.status.toLowerCase()}">${statusArr[heartbeat.status]}</span></td>
                <td>${heartbeat.executionTime}</td>
                <td>${heartbeat.lastExecuted}</td>
                <td><button type="button" class="btn btn-primary viewDetails" data-id="${heartbeat.id}">
                    View Details
                </button></td>
            `;
            tbody.appendChild(row);
        });
    },
    // Sort functionality
    sortData: function(data, column, direction) {
        return [...data].sort((a, b) => {
            let valueA = a[column];
            let valueB = b[column];
            
            // Remove special characters for numeric comparisons
            if (column === 'uptime' || column === 'executionTime') {
                valueA = parseFloat(valueA.replace(/[^0-9.]/g, ''));
                valueB = parseFloat(valueB.replace(/[^0-9.]/g, ''));
            }
            
            if (direction === 'asc') {
                return valueA > valueB ? 1 : -1;
            } else {
                return valueA < valueB ? 1 : -1;
            }
        });
    },
    //Setup search functionality
    setupSearch: function() {
        document.getElementById('heartbeatSearch').addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            const filteredSites = this.data.filter(heartbeat => 
                heartbeat.title.toLowerCase().includes(searchTerm) ||
                heartbeat.status.toLowerCase().includes(searchTerm)
            );
            this.renderTable(this.sortData(filteredSites, this.sort.column, this.sort.direction));
        });
    },
    //Setup sort event listeners
    setupSorting: function() {
        document.querySelectorAll('th[data-sort]').forEach(th => {
            th.addEventListener('click', () => {
                const column = th.dataset.sort;
                
                // Update sort direction
                if (this.sort.column === column) {
                    this.sort.direction = this.sort.direction === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sort.column = column;
                    this.sort.direction = 'asc';
                }

                // Update sort icons
                document.querySelectorAll('th[data-sort]').forEach(header => {
                    header.classList.remove('active', 'asc', 'desc');
                });
                th.classList.add('active', this.sort.direction);

                // Sort and render
                const searchTerm = document.getElementById('heartbeatSearch').value.toLowerCase();
                const filteredSites = this.data.filter(heartbeat => 
                    heartbeat.title.toLowerCase().includes(searchTerm) ||
                    heartbeat.status.toLowerCase().includes(searchTerm)
                );
                this.renderTable(this.sortData(filteredSites, this.sort.column, this.sort.direction));
            });
        });
    }
};

// Define the modal handler
const HeartbeatDetailsModal = {
    modal: null,
    init: function() {
        this.modal = $('#heartbeatDetailsModal');
        this.bindEvents();
    },
    
    bindEvents: function() {
        console.log('binding events');

        // Close modal when clicking the X or outside the modal
        $(document).on('click', '.close-modal, .modal-close-btn', () => this.hideModal());
        
        // Handle click outside modal
        $(document).on('click', (e) => {
            if ($(e.target).is('.heartbeat-details-modal')) {
                this.hideModal();
            }
        });

        // Handle view details button click
        $(document).on('click', '.viewDetails', (e) => this.handleViewDetails(e));
    },
    
    handleViewDetails: function(e) {
        const id = $(e.currentTarget).data('id');
        this.fetchTaskDetails(id);
    },
    
    fetchTaskDetails: async function(id) {

        try {
            await ajax_post(`ajax_get_heartbeat_details`, { id: id }, (res) => {  // Changed to arrow function
                this.showTaskDetails(res.data);
            });


        } catch (error) {
            console.error('Error fetching heartbeat details:', error);
            show_error('Failed to load heartbeat details');
        }

    },
    
    showTaskDetails: function(data) {
        // Update modal content with the data
        $('#taskStatus').text(data.status || 'N/A');
        $('#taskStartTime').text(data.startTime || 'N/A');
        $('#taskEndTime').text(data.endTime || 'N/A');
        $('#taskAttempts').text(data.attempts || 'N/A');
        $('#taskExecutionTime').text(data.executionTime || 'N/A');
        
        // Format JSON for metadata and fail details
        const metadata = typeof data.metadata === 'string' 
            ? JSON.parse(data.metadata) 
            : data.metadata;
        $('#taskMetadata').text(
            JSON.stringify(metadata || {}, null, 2)
        );
        $('#taskFailDetails').text(data.failDetails || 'No errors');

        // Update status with appropriate styling
        const statusBanner = $('#taskStatusBanner');
        statusBanner.removeClass('in-progress completed failed');
        
        const status = (data.status || '').toLowerCase();
        $('#taskStatus').text(data.status || 'N/A');
        
        if (status.includes('progress')) {
            statusBanner.addClass('in-progress');
        } else if (status.includes('complete')) {
            statusBanner.addClass('completed');
        } else if (status.includes('fail')) {
            statusBanner.addClass('failed');
        }
        
        this.showModal();
    },
    
    showModal: function() {
        this.modal.fadeIn(200);
        $('body').css('overflow', 'hidden');
    },
    
    hideModal: function() {
        this.modal.fadeOut(200);
        $('body').css('overflow', '');
    }
};