document.addEventListener('DOMContentLoaded', function() {
    // Get references to all forms and inputs
    const searchForm = document.querySelector('form[action*="app_event_list"]');
    const allForms = document.querySelectorAll('.filter-box form');
    const sortCheckbox = document.getElementById('filter-sort');
    const typeSelect = document.getElementById('filter-type');
    const closeSearch = document.getElementById('close-search');
    const searchResults = document.getElementById('search-results');
    const queryElement = document.getElementById('query');
    const eventsWrapper = document.getElementById('events-wrapper');
    
    // Helper function to update URL parameters without page reload
    function updateUrlParams(params) {
        const url = new URL(window.location);
        
        // Update parameters (don't clear existing ones to preserve others)
        Object.entries(params).forEach(([key, value]) => {
            if (value) {
                url.searchParams.set(key, value);
            } else {
                url.searchParams.delete(key);
            }
        });
        
        // Update browser history without reloading
        window.history.pushState({}, '', url);
    }
    
    // Function to collect all filter parameters from all inputs
    function collectFilterParams() {
        return {
            q: document.getElementById('filter-q') ? document.getElementById('filter-q').value : '',
            sort_by_date: sortCheckbox && sortCheckbox.checked ? '1' : '',
            event_type: typeSelect ? typeSelect.value : '',
            page: new URLSearchParams(window.location.search).get('page') || '1' // Preserve pagination
        };
    }
    
    // Function to fetch and update events
    function fetchEvents(params) {
        // Show loading indicator
        const eventsCol = document.querySelector('.col-md-9');
        
        // Create loading indicator
        const loadingHtml = `
            <div class="text-center p-5">
                <i class="fas fa-spinner fa-spin fa-3x"></i>
                <p class="mt-3">Chargement des événements...</p>
            </div>
        `;
        
        // Insert loading indicator
        eventsCol.innerHTML = loadingHtml;
        
        // Prepare URL for fetch
        const url = new URL(window.location.origin + '/event/searchevent');
        
        // Add parameters
        for (const [key, value] of Object.entries(params)) {
            if (value) {
                url.searchParams.set(key, value);
            }
        }
        
        // Add AJAX identifier
        url.searchParams.set('ajax', '1');
        
        // Fetch events
        fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            // Update the content
            eventsCol.innerHTML = html;
            
            // Re-attach pagination event listeners
            attachPaginationHandlers();
            
            // Reinitialize modals and other event handlers
            initializeEventHandlers();
        })
        .catch(error => {
            console.error('Error fetching events:', error);
            eventsCol.innerHTML = `
                <div class="alert alert-danger">
                    Erreur lors du chargement des événements. <br>
                    <button class="btn btn-outline-danger mt-2" onclick="window.location.reload()">Recharger la page</button>
                </div>
            `;
        });
    }
    
    // Function to fetch events after deleting an event
    function fetchDeletedEvents(eventId) {
        // Show loading indicator
        const eventsCol = document.querySelector('.col-md-9');
        
        // Create loading indicator
        const loadingHtml = `
            <div class="text-center p-5">
                <i class="fas fa-spinner fa-spin fa-3x"></i>
                <p class="mt-3">Chargement des événements...</p>
            </div>
        `;
        
        // Insert loading indicator
        eventsCol.innerHTML = loadingHtml;
        
        // Prepare URL for fetch
        const url = new URL(window.location.origin + `/event/${eventId}`);
        
        // Add AJAX identifier
        url.searchParams.set('ajax', '1');
        
        // Fetch events
        fetch(url.toString(), {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            // Update the content
            eventsCol.innerHTML = html;
            
            // Re-attach pagination event handlers
            attachPaginationHandlers();
            
            // Reinitialize modals and other event handlers
            initializeEventHandlers();
        })
        .catch(error => {
            console.error('Error fetching events:', error);
            eventsCol.innerHTML = `
                <div class="alert alert-danger">
                    Erreur lors du chargement des événements. <br>
                    <button class="btn btn-outline-danger mt-2" onclick="window.location.reload()">Recharger la page</button>
                </div>
            `;
        });
    }
    
    // Function to initialize event handlers for newly loaded content
    function initializeEventHandlers() {
        // Re-initialize Bootstrap modals
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            new bootstrap.Modal(modal);
        });
        
        // Re-attach delete button handlers
        const deleteButtons = document.querySelectorAll('.btn-delete-icon');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                const eventId = this.getAttribute('data-id');
                if (eventId) {
                    fetchDeletedEvents(eventId);
                }
            });
        });
    }
    
    // Function to attach pagination handlers
    function attachPaginationHandlers() {
        const paginationLinks = document.querySelectorAll('.pagination a.page-link');
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const href = this.getAttribute('href');
                if (href) {
                    const url = new URL(href, window.location.origin);
                    const page = url.searchParams.get('page') || '1';
                    
                    // Get current filters
                    const params = collectFilterParams();
                    // Update page number
                    params.page = page;
                    
                    // Update URL
                    updateUrlParams(params);
                    
                    // Fetch events for this page
                    fetchEvents(params);
                    
                    // Scroll to top of events
                    document.querySelector('.events-container').scrollTo(0, 0);
                }
            });
        });
    }
    
    // Stop form submissions and handle them with AJAX
    allForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent the default form submission
            
            // Reset to page 1 when searching/filtering
            const params = collectFilterParams();
            params.page = '1';
            
            updateUrlParams(params);
            
            // Show search results indicator if searching
            if (params.q) {
                searchResults.classList.remove('hidden');
                if (queryElement) {
                    queryElement.textContent = params.q;
                }
            } else {
                searchResults.classList.add('hidden');
            }
            
            fetchEvents(params);
        });
    });
    
    // Handle filter checkbox change
    if (sortCheckbox) {
        sortCheckbox.removeAttribute('onchange');
        sortCheckbox.addEventListener('change', function() {
            // Reset to page 1 when changing filters
            const params = collectFilterParams();
            params.page = '1';
            
            updateUrlParams(params);
            fetchEvents(params);
        });
    }
    
    // Handle event type selection change
    if (typeSelect) {
        typeSelect.removeAttribute('onchange');
        typeSelect.addEventListener('change', function() {
            // Reset to page 1 when changing filters
            const params = collectFilterParams();
            params.page = '1';
            
            updateUrlParams(params);
            fetchEvents(params);
        });
    }
    
    // Handle close search button
    if (closeSearch) {
        closeSearch.addEventListener('click', function() {
            searchResults.classList.add('hidden');
            
            // Clear search query but keep other filters
            const params = collectFilterParams();
            params.q = '';
            params.page = '1'; // Reset to page 1
            
            // Clear the search input field
            if (document.getElementById('filter-q')) {
                document.getElementById('filter-q').value = '';
            }
            
            updateUrlParams(params);
            fetchEvents(params);
        });
    }
    
    // Handle back/forward navigation
    window.addEventListener('popstate', function() {
        // Extract parameters from current URL
        const url = new URL(window.location);
        const params = {
            q: url.searchParams.get('q') || '',
            sort_by_date: url.searchParams.get('sort_by_date') || '',
            event_type: url.searchParams.get('event_type') || '',
            page: url.searchParams.get('page') || '1'
        };
        
        // Update form inputs to match URL parameters
        if (document.getElementById('filter-q')) {
            document.getElementById('filter-q').value = params.q;
        }
        
        if (sortCheckbox) {
            sortCheckbox.checked = !!params.sort_by_date;
        }
        
        if (typeSelect) {
            typeSelect.value = params.event_type;
        }
        
        // Show/hide search results indicator
        if (params.q) {
            searchResults.classList.remove('hidden');
            if (queryElement) {
                queryElement.textContent = params.q;
            }
        } else {
            searchResults.classList.add('hidden');
        }
        
        // Fetch events with these parameters
        fetchEvents(params);
    });
    
    // Initial setup - attach handlers to existing pagination
    attachPaginationHandlers();
    initializeEventHandlers();
});