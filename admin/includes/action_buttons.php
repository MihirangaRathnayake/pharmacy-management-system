<?php
/**
 * Action Buttons Helper
 * Provides reusable action button components for CRUD operations
 */

/**
 * Generate action buttons for a record
 * 
 * @param array $config Configuration array with the following keys:
 *   - id: Record ID
 *   - name: Record name (for delete confirmation)
 *   - module: Module name (e.g., 'inventory', 'customers')
 *   - actions: Array of actions to include ['view', 'edit', 'delete']
 *   - custom_actions: Array of custom actions with format:
 *     ['label' => 'Custom', 'url' => 'custom.php?id={id}', 'class' => 'bg-purple-100 text-purple-700', 'icon' => 'fas fa-star']
 * @return string HTML for action buttons
 */
function renderActionButtons($config) {
    $id = $config['id'];
    $name = htmlspecialchars($config['name'] ?? 'Record', ENT_QUOTES);
    $module = $config['module'];
    $actions = $config['actions'] ?? ['view', 'edit', 'delete'];
    $customActions = $config['custom_actions'] ?? [];
    
    $html = '<div class="flex space-x-1">';
    
    // Standard actions
    foreach ($actions as $action) {
        switch ($action) {
            case 'view':
                $html .= sprintf(
                    '<a href="view_%s.php?id=%d" class="action-btn inline-flex items-center px-2 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 text-xs font-medium rounded-md" title="View Details">
                        <i class="fas fa-eye mr-1"></i>View
                    </a>',
                    $module === 'inventory' ? 'medicine' : $module,
                    $id
                );
                break;
                
            case 'edit':
                $html .= sprintf(
                    '<a href="edit_%s.php?id=%d" class="action-btn inline-flex items-center px-2 py-1 bg-green-100 hover:bg-green-200 text-green-700 text-xs font-medium rounded-md" title="Edit Record">
                        <i class="fas fa-edit mr-1"></i>Edit
                    </a>',
                    $module === 'inventory' ? 'medicine' : $module,
                    $id
                );
                break;
                
            case 'delete':
                $deleteFunction = $module === 'inventory' ? 'deleteMedicine' : 'deleteRecord';
                $html .= sprintf(
                    '<button onclick="%s(%d, \'%s\')" class="action-btn inline-flex items-center px-2 py-1 bg-red-100 hover:bg-red-200 text-red-700 text-xs font-medium rounded-md" title="Delete Record">
                        <i class="fas fa-trash mr-1"></i>Delete
                    </button>',
                    $deleteFunction,
                    $id,
                    $name
                );
                break;
        }
    }
    
    // Custom actions
    foreach ($customActions as $customAction) {
        $url = str_replace('{id}', $id, $customAction['url']);
        $html .= sprintf(
            '<a href="%s" class="action-btn inline-flex items-center px-2 py-1 %s text-xs font-medium rounded-md" title="%s">
                <i class="%s mr-1"></i>%s
            </a>',
            $url,
            $customAction['class'],
            $customAction['label'],
            $customAction['icon'],
            $customAction['label']
        );
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Generate delete confirmation modal HTML
 * 
 * @param string $entityName Name of the entity being deleted (e.g., 'Medicine', 'Customer')
 * @return string HTML for delete modal
 */
function renderDeleteModal($entityName = 'Record') {
    return sprintf('
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 modal-enter">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-gray-900">Delete %s</h3>
                </div>
            </div>
            <div class="mb-4">
                <p class="text-sm text-gray-500">
                    Are you sure you want to delete "<span id="recordName" class="font-medium"></span>"? 
                    This action cannot be undone.
                </p>
                <p class="text-xs text-gray-400 mt-2">
                    Note: If this %s has related records, it may be marked as inactive instead of being deleted.
                </p>
            </div>
            <div class="flex justify-end space-x-3">
                <button onclick="closeDeleteModal()" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors duration-200">
                    Cancel
                </button>
                <button onclick="confirmDelete()" 
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-md transition-colors duration-200">
                    <i class="fas fa-trash mr-1"></i>
                    Delete
                </button>
            </div>
        </div>
    </div>', $entityName, strtolower($entityName));
}

/**
 * Generate toast notification HTML
 * 
 * @return string HTML for toast notification
 */
function renderToast() {
    return '
    <!-- Success/Error Toast -->
    <div id="toast" class="fixed top-4 right-4 z-50 hidden">
        <div class="bg-white border border-gray-200 rounded-lg shadow-lg p-4 max-w-sm toast-enter">
            <div class="flex items-center">
                <div id="toastIcon" class="flex-shrink-0"></div>
                <div class="ml-3">
                    <p id="toastMessage" class="text-sm font-medium"></p>
                </div>
                <button onclick="hideToast()" class="ml-4 text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>';
}

/**
 * Generate JavaScript for delete functionality
 * 
 * @param string $deleteEndpoint The PHP file that handles deletion (e.g., 'delete_medicine.php')
 * @param string $functionName The JavaScript function name (e.g., 'deleteMedicine')
 * @return string JavaScript code
 */
function renderDeleteScript($deleteEndpoint, $functionName = 'deleteRecord') {
    return sprintf('
    <script>
        let currentRecordId = null;

        function %s(id, name) {
            currentRecordId = id;
            document.getElementById("recordName").textContent = name;
            document.getElementById("deleteModal").classList.remove("hidden");
            document.getElementById("deleteModal").classList.add("flex");
        }

        function closeDeleteModal() {
            document.getElementById("deleteModal").classList.add("hidden");
            document.getElementById("deleteModal").classList.remove("flex");
            currentRecordId = null;
        }

        function confirmDelete() {
            if (!currentRecordId) return;

            // Show loading state
            const deleteBtn = document.querySelector("#deleteModal button[onclick=\"confirmDelete()\"]");
            const originalText = deleteBtn.innerHTML;
            deleteBtn.innerHTML = "<i class=\"fas fa-spinner fa-spin mr-1\"></i>Deleting...";
            deleteBtn.disabled = true;

            fetch("%s", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ id: currentRecordId })
            })
            .then(response => response.json())
            .then(data => {
                closeDeleteModal();
                
                if (data.success) {
                    showToast(data.message, "success");
                    // Reload page after short delay to show toast
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showToast("Error: " + data.message, "error");
                }
            })
            .catch(error => {
                closeDeleteModal();
                showToast("Network error occurred", "error");
            })
            .finally(() => {
                // Reset button state
                deleteBtn.innerHTML = originalText;
                deleteBtn.disabled = false;
            });
        }

        function showToast(message, type) {
            const toast = document.getElementById("toast");
            const toastIcon = document.getElementById("toastIcon");
            const toastMessage = document.getElementById("toastMessage");

            // Set icon and colors based on type
            if (type === "success") {
                toastIcon.innerHTML = "<i class=\"fas fa-check-circle text-green-600 text-lg\"></i>";
                toastMessage.className = "text-sm font-medium text-green-800";
            } else {
                toastIcon.innerHTML = "<i class=\"fas fa-exclamation-circle text-red-600 text-lg\"></i>";
                toastMessage.className = "text-sm font-medium text-red-800";
            }

            toastMessage.textContent = message;
            toast.classList.remove("hidden");

            // Auto hide after 5 seconds
            setTimeout(hideToast, 5000);
        }

        function hideToast() {
            document.getElementById("toast").classList.add("hidden");
        }

        // Close modal when clicking outside
        document.getElementById("deleteModal").addEventListener("click", function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener("keydown", function(e) {
            if (e.key === "Escape") {
                closeDeleteModal();
            }
        });
    </script>', $functionName, $deleteEndpoint);
}

/**
 * Generate CSS for action buttons and animations
 * 
 * @return string CSS code
 */
function renderActionButtonsCSS() {
    return '
    <style>
        /* Enhanced button animations */
        .action-btn {
            transition: all 0.2s ease-in-out;
            transform: translateY(0);
        }
        
        .action-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .action-btn:active {
            transform: translateY(0);
        }
        
        /* Table row hover effect */
        .table-row:hover {
            background-color: #f8fafc;
            transform: translateX(2px);
            transition: all 0.2s ease-in-out;
        }
        
        /* Status badge animations */
        .status-badge {
            transition: all 0.2s ease-in-out;
        }
        
        .status-badge:hover {
            transform: scale(1.05);
        }
        
        /* Alert icons pulse animation */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .alert-icon {
            animation: pulse 2s infinite;
        }
        
        /* Modal animations */
        .modal-enter {
            animation: modalEnter 0.3s ease-out;
        }
        
        @keyframes modalEnter {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        /* Toast animations */
        .toast-enter {
            animation: toastEnter 0.3s ease-out;
        }
        
        @keyframes toastEnter {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
    </style>';
}
?>