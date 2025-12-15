/**
 * Bitrix24 Module: Responsible Role
 * Frontend JavaScript component
 */

(function(window) {
    'use strict';

    var ResponsibleRole = function(options) {
        this.options = options || {};
        this.taskId = this.options.taskId || null;
        this.fieldName = this.options.fieldName || 'UF_RESPONSIBLE_EMPLOYEE';
        this.users = this.options.users || {};
        this.currentUser = this.options.currentUser || null;
        this.init();
    };

    ResponsibleRole.prototype.init = function() {
        this.initializeField();
        this.attachEventHandlers();
    };

    /**
     * Initialize responsible employee field
     */
    ResponsibleRole.prototype.initializeField = function() {
        var self = this;
        var fieldContainer = document.getElementById('responsible-role-field');

        if (!fieldContainer) {
            return;
        }

        // Create field HTML
        var html = '<div class="responsible-role-wrapper">' +
            '<label for="responsible-employee-select" class="responsible-role-label">' +
            'Ответственный сотрудник' +
            '</label>' +
            '<select id="responsible-employee-select" class="responsible-role-select" name="' + this.fieldName + '">' +
            '<option value="">-- Выберите сотрудника --</option>';

        // Add users to dropdown
        for (var userId in this.users) {
            if (this.users.hasOwnProperty(userId)) {
                var selected = (this.currentUser == userId) ? ' selected="selected"' : '';
                html += '<option value="' + userId + '"' + selected + '>' + 
                    this.users[userId] + 
                    '</option>';
            }
        }

        html += '</select>' +
            '<div class="responsible-role-info"></div>' +
            '</div>';

        fieldContainer.innerHTML = html;
    };

    /**
     * Attach event handlers
     */
    ResponsibleRole.prototype.attachEventHandlers = function() {
        var self = this;
        var selectElement = document.getElementById('responsible-employee-select');

        if (!selectElement) {
            return;
        }

        selectElement.addEventListener('change', function(e) {
            self.onUserSelected(e.target.value);
        });
    };

    /**
     * Handle user selection
     */
    ResponsibleRole.prototype.onUserSelected = function(userId) {
        var infoDiv = document.querySelector('.responsible-role-info');

        if (!infoDiv) {
            return;
        }

        if (userId) {
            var userName = this.users[userId] || '';
            infoDiv.innerHTML = '<p class="info-message">Выбран: <strong>' + userName + '</strong></p>';
            infoDiv.style.display = 'block';
        } else {
            infoDiv.innerHTML = '';
            infoDiv.style.display = 'none';
        }

        // Trigger change event for form tracking
        this.triggerFormChange();
    };

    /**
     * Trigger form change event
     */
    ResponsibleRole.prototype.triggerFormChange = function() {
        var event = new Event('change', { bubbles: true });
        var selectElement = document.getElementById('responsible-employee-select');

        if (selectElement) {
            selectElement.dispatchEvent(event);
        }
    };

    /**
     * Get selected user ID
     */
    ResponsibleRole.prototype.getSelectedUser = function() {
        var selectElement = document.getElementById('responsible-employee-select');

        if (selectElement) {
            return selectElement.value;
        }

        return null;
    };

    /**
     * Set selected user ID
     */
    ResponsibleRole.prototype.setSelectedUser = function(userId) {
        var selectElement = document.getElementById('responsible-employee-select');

        if (selectElement) {
            selectElement.value = userId;
            this.onUserSelected(userId);
        }
    };

    /**
     * Display task details with responsible employee
     */
    ResponsibleRole.prototype.displayTaskDetails = function(taskData) {
        var detailsContainer = document.getElementById('task-responsible-details');

        if (!detailsContainer || !taskData.responsibleEmployee) {
            return;
        }

        var html = '<div class="task-responsible-section">' +
            '<h4>Ответственный</h4>' +
            '<div class="responsible-employee-info">' +
            '<span class="employee-name">' + taskData.responsibleEmployee.name + '</span>' +
            '<span class="employee-email">(' + taskData.responsibleEmployee.email + ')</span>' +
            '</div>' +
            '</div>';

        detailsContainer.innerHTML = html;
    };

    /**
     * Apply filter by responsible employee
     */
    ResponsibleRole.prototype.applyFilter = function(userId) {
        var filterInput = document.getElementById('filter-responsible-employee');

        if (filterInput) {
            filterInput.value = userId;
            this.triggerFilterSubmit();
        }
    };

    /**
     * Trigger filter submission
     */
    ResponsibleRole.prototype.triggerFilterSubmit = function() {
        var filterForm = document.getElementById('task-filter-form');

        if (filterForm) {
            filterForm.submit();
        }
    };

    /**
     * Get tasks by responsible employee (AJAX)
     */
    ResponsibleRole.prototype.getTasksByResponsible = function(userId, callback) {
        var self = this;

        BX.ajax.runComponentAction(
            'bitrix:tasks.task.list',
            'getTasksByResponsible',
            {
                mode: 'class',
                data: {
                    userId: userId
                }
            }
        ).then(function(response) {
            if (callback && typeof callback === 'function') {
                callback(response.data);
            }
        }).catch(function(error) {
            console.error('Error loading tasks:', error);
        });
    };

    /**
     * Export to global scope
     */
    window.ResponsibleRole = ResponsibleRole;

})(window);

/**
 * Initialize on document ready
 */
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        if (window.RESPONSIBLE_ROLE_CONFIG) {
            new ResponsibleRole(window.RESPONSIBLE_ROLE_CONFIG);
        }
    });
} else {
    if (window.RESPONSIBLE_ROLE_CONFIG) {
        new ResponsibleRole(window.RESPONSIBLE_ROLE_CONFIG);
    }
}
