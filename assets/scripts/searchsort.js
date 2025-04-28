        // Insert the improved JavaScript code here
        document.addEventListener('DOMContentLoaded', function() {
            // Get references to all forms and inputs
            const searchForm = document.querySelector('form[action*="app_event_list"]');
            const allForms = document.querySelectorAll('.filter-box form');
            const sortCheckbox = document.getElementById('filter-sort');
            const typeSelect = document.getElementById('filter-type');
            const closeSearch = document.getElementById('close-search');
            const searchResults = document.getElementById('search-results');
            var queryInput = document.getElementById('query');
            const eventsContainer = document.querySelector('.events-container');
            
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
                    event_type: typeSelect ? typeSelect.value : ''
                };
            }
            
            // Function to fetch and update events
            function fetchEvents(params) {
                // Show loading indicator
                const eventsCol = document.querySelector('.col-md-9');
                const originalContent = eventsCol.innerHTML;
                
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
                const url = new URL(window.location.origin + window.location.pathname);
                
                // Add base path for event list
                if (!url.pathname.includes('searchevent')) {
                    url.pathname = '/event/searchevent';
                }
                
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
            }
            
            // Stop form submissions and handle them with AJAX
            allForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault(); // This is crucial - prevent the default form submission
                    searchResults.classList.remove('hidden');
                    const params = collectFilterParams();
                    updateUrlParams(params);
                    queryInput.textContent = document.getElementById('filter-q').value;
                    fetchEvents(params);
                });
            });
            
            // Handle filter checkbox change - prevent default onchange behavior
            if (sortCheckbox) {
                // Remove any existing onchange attribute
                sortCheckbox.removeAttribute('onchange');
                
                // Add our event listener
                sortCheckbox.addEventListener('change', function(e) {
                    const params = collectFilterParams();
                    updateUrlParams(params);
                    fetchEvents(params);
                });
            }
            
            // Handle event type selection change - prevent default onchange behavior
            if (typeSelect) {
                // Remove any existing onchange attribute
                typeSelect.removeAttribute('onchange');
                
                // Add our event listener
                typeSelect.addEventListener('change', function(e) {
                    const params = collectFilterParams();
                    updateUrlParams(params);
                    fetchEvents(params);
                });
            }
            if(closeSearch) {
                closeSearch.addEventListener('click', function() {
                    searchResults.classList.add('hidden');
                    console.log('Close search clicked');
                    const params = collectFilterParams();
                    params.q = ''; // Clear search query
                    document.getElementById('filter-q').value=''; // Clear search query
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
                    event_type: url.searchParams.get('event_type') || ''
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
                
                // Fetch events with these parameters
                fetchEvents(params);
            });
        });