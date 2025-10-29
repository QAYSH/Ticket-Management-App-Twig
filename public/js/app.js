// Global app JavaScript functionality

// Toast notification system
class Toast {
    static show(message, type = 'info', duration = 5000) {
        const toast = document.createElement('div');
        toast.className = `p-4 rounded-lg shadow-lg border transform transition-all duration-300 ${
            type === 'success' ? 'bg-status-open text-white border-status-open/20' :
            type === 'error' ? 'bg-destructive text-white border-destructive/20' :
            type === 'warning' ? 'bg-status-in-progress text-white border-status-in-progress/20' :
            'bg-primary text-white border-primary/20'
        }`;
        toast.textContent = message;
        
        const container = document.getElementById('toast-container');
        if (!container) return;
        
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, duration);
    }
}

// Make Toast globally available
window.Toast = Toast;

// Form validation helpers
class FormValidator {
    static validateRequired(value, fieldName) {
        if (!value || value.trim() === '') {
            return `${fieldName} is required`;
        }
        return null;
    }

    static validateEmail(email) {
        if (!email) return 'Email is required';
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            return 'Invalid email format';
        }
        return null;
    }

    static validateMinLength(value, minLength, fieldName) {
        if (value && value.length < minLength) {
            return `${fieldName} must be at least ${minLength} characters`;
        }
        return null;
    }

    static validateMaxLength(value, maxLength, fieldName) {
        if (value && value.length > maxLength) {
            return `${fieldName} must be less than ${maxLength} characters`;
        }
        return null;
    }
}

// API service for making requests
class ApiService {
    static async request(url, options = {}) {
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
            },
        };

        const config = { ...defaultOptions, ...options };

        try {
            const response = await fetch(url, config);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Request failed');
            }

            return data;
        } catch (error) {
            console.error('API request failed:', error);
            throw error;
        }
    }

    static async get(url) {
        return this.request(url, { method: 'GET' });
    }

    static async post(url, data) {
        return this.request(url, {
            method: 'POST',
            body: JSON.stringify(data),
        });
    }

    static async put(url, data) {
        return this.request(url, {
            method: 'PUT',
            body: JSON.stringify(data),
        });
    }

    static async delete(url) {
        return this.request(url, { method: 'DELETE' });
    }
}

// Mobile menu functionality
document.addEventListener('DOMContentLoaded', function() {
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(event) {
        const mobileMenu = document.getElementById('mobileMenu');
        const menuButton = event.target.closest('button[aria-label="Toggle menu"]');
        
        if (mobileMenu && !mobileMenu.contains(event.target) && !menuButton) {
            mobileMenu.classList.add('hidden');
        }
    });

    // Escape key to close modals
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const formModal = document.getElementById('formModal');
            const deleteModal = document.getElementById('deleteModal');
            
            if (formModal && !formModal.classList.contains('hidden')) {
                window.closeForm?.();
            }
            
            if (deleteModal && !deleteModal.classList.contains('hidden')) {
                window.closeDeleteModal?.();
            }
        }
    });
});

// Export for global use
window.FormValidator = FormValidator;
window.ApiService = ApiService;