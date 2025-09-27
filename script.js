// ManifestLink Main JavaScript
document.addEventListener('DOMContentLoaded', function() {
    
    // Enhanced Mobile Navigation Toggle
const hamburger = document.querySelector('.hamburger');
const navMenu = document.querySelector('.nav-menu');
    const body = document.body;

    if (hamburger && navMenu) {
        hamburger.addEventListener('click', function() {
    hamburger.classList.toggle('active');
    navMenu.classList.toggle('active');
            body.style.overflow = navMenu.classList.contains('active') ? 'hidden' : '';
});

// Close mobile menu when clicking on a link
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
    hamburger.classList.remove('active');
    navMenu.classList.remove('active');
                body.style.overflow = '';
            });
        });
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!hamburger.contains(e.target) && !navMenu.contains(e.target)) {
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
                body.style.overflow = '';
            }
        });
        
        // Close mobile menu on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && navMenu.classList.contains('active')) {
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
                body.style.overflow = '';
            }
        });
    }
    
    // Enhanced Navbar Background on Scroll
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        let lastScrollTop = 0;
        
        window.addEventListener('scroll', function() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            // Add/remove scrolled class for enhanced styling
            if (scrollTop > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
            
            // Hide/show navbar on scroll (optional)
            if (scrollTop > lastScrollTop && scrollTop > 100) {
                // Scrolling down
                navbar.style.transform = 'translateY(-100%)';
            } else {
                // Scrolling up
                navbar.style.transform = 'translateY(0)';
            }
            
            lastScrollTop = scrollTop;
        });
    }
    
    // Active Navigation Link Highlighting
    function updateActiveNavLink() {
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.nav-link[href^="#"]');
        
        let current = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop - 100;
            const sectionHeight = section.clientHeight;
            if (window.pageYOffset >= sectionTop && window.pageYOffset < sectionTop + sectionHeight) {
                current = section.getAttribute('id');
            }
        });
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${current}`) {
                link.classList.add('active');
            }
        });
    }
    
    // Smooth Scrolling for Navigation Links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
                const offsetTop = target.offsetTop - 80; // Account for fixed navbar
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
            });
        }
    });
});

    // Update active nav link on scroll
    window.addEventListener('scroll', updateActiveNavLink);
    
    // Initialize active nav link
    updateActiveNavLink();
    
    // Scroll Reveal Animation
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

    const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
        }
    });
}, observerOptions);

    // Observe elements for scroll reveal
    document.querySelectorAll('.feature-card, .step, .contact-item, .badge').forEach(el => {
        el.classList.add('scroll-reveal');
        observer.observe(el);
});

    // Contact Form Handling
const contactForm = document.querySelector('.contact-form');
if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get form data
            const formData = new FormData(this);
            const name = this.querySelector('input[type="text"]').value;
            const email = this.querySelector('input[type="email"]').value;
            const subject = this.querySelectorAll('input[type="text"]')[1].value;
            const message = this.querySelector('textarea').value;
            
            // Basic validation
            if (!name || !email || !subject || !message) {
                showNotification('Please fill in all fields', 'error');
            return;
        }
        
            if (!isValidEmail(email)) {
                showNotification('Please enter a valid email address', 'error');
            return;
        }
        
        // Simulate form submission
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Sending...';
        submitBtn.disabled = true;
        
        // Simulate API call
        setTimeout(() => {
                showNotification('Thank you! Your message has been sent successfully.', 'success');
                this.reset();
                submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }, 2000);
    });
}

    // Email validation function
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // Notification system
    function showNotification(message, type = 'info') {
        // Remove existing notifications
        const existingNotification = document.querySelector('.notification');
        if (existingNotification) {
            existingNotification.remove();
        }
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <span class="notification-message">${message}</span>
                <button class="notification-close">&times;</button>
            </div>
        `;
        
        // Add styles
        notification.style.cssText = `
            position: fixed;
            top: 100px;
            right: 20px;
            background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 10000;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            max-width: 400px;
        `;
        
        // Add to page
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Close button functionality
        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', () => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        });
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => notification.remove(), 300);
            }
        }, 5000);
    }
    
    // Counter Animation for Stats
    function animateCounters() {
        const counters = document.querySelectorAll('.stat-number');
        counters.forEach(counter => {
            const target = counter.textContent;
            const isPercentage = target.includes('%');
            const isTime = target.includes('/');
            
            let finalValue;
            if (isPercentage) {
                finalValue = parseInt(target);
            } else if (isTime) {
                finalValue = target; // Keep as is for time values
            } else {
                finalValue = parseInt(target);
            }
            
            if (!isTime) {
                let currentValue = 0;
                const increment = finalValue / 50;
                
                const updateCounter = () => {
                    if (currentValue < finalValue) {
                        currentValue += increment;
                        counter.textContent = isPercentage ? 
                            Math.ceil(currentValue) + '%' : 
                            Math.ceil(currentValue);
                        requestAnimationFrame(updateCounter);
        } else {
                        counter.textContent = target;
                    }
                };
                
                updateCounter();
            }
        });
    }
    
    // Trigger counter animation when stats section is visible
    const statsSection = document.querySelector('.hero-stats');
    if (statsSection) {
        const statsObserver = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
                    animateCounters();
                    statsObserver.unobserve(entry.target);
        }
    });
}, { threshold: 0.5 });

        statsObserver.observe(statsSection);
    }

// Parallax effect for hero section
    function parallaxEffect() {
        const hero = document.querySelector('.hero');
        if (hero) {
    const scrolled = window.pageYOffset;
            const rate = scrolled * -0.5;
            hero.style.transform = `translateY(${rate}px)`;
        }
    }
    
    // Throttle function for performance
    function throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        }
    }
    
    // Apply throttled parallax effect
    window.addEventListener('scroll', throttle(parallaxEffect, 16));
    
    // Accessibility improvements
    function enhanceAccessibility() {
        // Add focus indicators
        const focusableElements = document.querySelectorAll('a, button, input, textarea, select');
        focusableElements.forEach(element => {
            element.addEventListener('focus', function() {
                this.style.outline = '2px solid var(--primary-color)';
                this.style.outlineOffset = '2px';
            });
            
            element.addEventListener('blur', function() {
                this.style.outline = 'none';
            });
        });
        
        // Add skip link for keyboard navigation
        const skipLink = document.createElement('a');
        skipLink.href = '#main-content';
        skipLink.textContent = 'Skip to main content';
        skipLink.style.cssText = `
            position: absolute;
            top: -40px;
            left: 6px;
            background: var(--primary-color);
            color: white;
            padding: 8px;
            text-decoration: none;
            border-radius: 4px;
            z-index: 10001;
        `;
        skipLink.addEventListener('focus', function() {
            this.style.top = '6px';
        });
        skipLink.addEventListener('blur', function() {
            this.style.top = '-40px';
        });
        
        document.body.insertBefore(skipLink, document.body.firstChild);
    }
    
    // Initialize accessibility features
    enhanceAccessibility();
    
    // Performance optimization: Lazy load images
    function lazyLoadImages() {
        const images = document.querySelectorAll('img[data-src]');
        const imageObserver = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        images.forEach(img => imageObserver.observe(img));
    }
    
    // Initialize lazy loading
    lazyLoadImages();
    
    // Service Worker Registration for PWA capabilities
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('/sw.js')
                .then(function(registration) {
                    console.log('ServiceWorker registration successful');
                })
                .catch(function(err) {
                    console.log('ServiceWorker registration failed');
                });
        });
    }
    
    // Offline functionality
    function setupOfflineSupport() {
        // Cache critical resources
        const criticalResources = [
            '/',
            '/css/style.css',
            '/css/responsive.css',
            '/js/main.js'
        ];
        
        // Store data in localStorage for offline access
        if (typeof(Storage) !== "undefined") {
            // Example: Store form data for offline submission
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('input', function() {
                    const formData = new FormData(this);
                    const formObject = {};
                    for (let [key, value] of formData.entries()) {
                        formObject[key] = value;
                    }
                    localStorage.setItem('offlineFormData', JSON.stringify(formObject));
                });
            });
        }
    }
    
    // Initialize offline support
    setupOfflineSupport();
    
    // Analytics tracking (example implementation)
    function trackEvent(eventName, eventData = {}) {
        // Example analytics tracking
        console.log('Event tracked:', eventName, eventData);
        
        // In a real implementation, you would send this to your analytics service
        // gtag('event', eventName, eventData);
    }
    
    // Track important user interactions
    document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('click', function() {
            trackEvent('button_click', {
                button_text: this.textContent.trim(),
                button_type: this.classList.contains('btn-primary') ? 'primary' : 'secondary'
            });
        });
    });
    
    // Track form submissions
    if (contactForm) {
        contactForm.addEventListener('submit', function() {
            trackEvent('contact_form_submit');
        });
    }
    
    // Track page views
    trackEvent('page_view', {
        page_title: document.title,
        page_url: window.location.href
    });
    
    console.log('ManifestLink website initialized successfully!');

    // === Smooth Fade-Out Page Transition ===
    function addFadeOutTransition() {
        // Intercept navigation for fade-out effect
        const transitionLinks = [
            // On index.html: Register Now button in nav and hero
            'a[href="register.php"]',
            // On register.php: logo and nav links back to index.html
            'a[href="index.html"]',
            'a[href="index.html#home"]',
            'a[href="index.html#features"]',
            'a[href="index.html#about"]',
            'a[href="index.html#contact"]'
        ];
        transitionLinks.forEach(selector => {
            document.querySelectorAll(selector).forEach(link => {
                link.addEventListener('click', function(e) {
                    // Only fade if not opening in new tab
                    if (!e.ctrlKey && !e.metaKey && !e.shiftKey && !e.altKey) {
                        e.preventDefault();
                        document.body.classList.add('fade-out');
                        setTimeout(() => {
                            window.location.href = link.getAttribute('href');
                        }, 500); // match CSS duration
                    }
                });
            });
        });
    }

    // Run after DOMContentLoaded
    addFadeOutTransition();
});

// === MANIFESTLINK ENHANCED USER EXPERIENCE ===

class ManifestLinkUX {
    constructor() {
        this.initializeComponents();
        this.bindEvents();
    }

    initializeComponents() {
        // Initialize loading overlay
        this.createLoadingOverlay();
        
        // Initialize toast container
        this.createToastContainer();
        
        // Initialize form validation
        this.initializeFormValidation();
        
        // Initialize progress indicators
        this.initializeProgressIndicators();
    }

    createLoadingOverlay() {
        const overlay = document.createElement('div');
        overlay.className = 'loading-overlay';
        overlay.innerHTML = `
            <div class="loading-content">
                <div class="loading-spinner"></div>
                <div class="loading-text">Processing your request...</div>
            </div>
        `;
        document.body.appendChild(overlay);
        this.loadingOverlay = overlay;
    }

    createToastContainer() {
        const container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
        this.toastContainer = container;
    }

    showLoading(message = 'Processing your request...') {
        if (this.loadingOverlay) {
            this.loadingOverlay.querySelector('.loading-text').textContent = message;
            this.loadingOverlay.classList.add('active');
        }
    }

    hideLoading() {
        if (this.loadingOverlay) {
            this.loadingOverlay.classList.remove('active');
        }
    }

    showToast(type, title, message, duration = 5000) {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        
        const iconMap = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };

        toast.innerHTML = `
            <i class="toast-icon ${iconMap[type] || iconMap.info}"></i>
            <div class="toast-content">
                <div class="toast-title">${title}</div>
                <div class="toast-message">${message}</div>
                </div>
            <button class="toast-close" aria-label="Close notification">
                <i class="fas fa-times"></i>
                    </button>
        `;

        this.toastContainer.appendChild(toast);

        // Show animation
        setTimeout(() => toast.classList.add('show'), 100);

        // Auto remove
        setTimeout(() => this.removeToast(toast), duration);

        // Close button
        toast.querySelector('.toast-close').addEventListener('click', () => {
            this.removeToast(toast);
        });

        return toast;
    }

    removeToast(toast) {
        toast.classList.remove('show');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }

    initializeFormValidation() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => this.setupFormValidation(form));
    }

    setupFormValidation(form) {
        const inputs = form.querySelectorAll('input, textarea, select');
        
        inputs.forEach(input => {
            // Add validation icons
            this.addValidationIcon(input);
            
            // Add input highlight
            this.addInputHighlight(input);
            
            // Real-time validation
            input.addEventListener('blur', () => this.validateField(input));
            input.addEventListener('input', () => this.clearFieldValidation(input));
        });

        // Form submission - temporarily simplified
        form.addEventListener('submit', (e) => {
            // Just show loading state and let form submit normally
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                this.setButtonLoading(submitBtn, true);
            }
            this.showLoading('Generating your QR code...');
            
            // Let the form submit normally - no preventDefault
        });
    }

    addValidationIcon(input) {
        const icon = document.createElement('i');
        icon.className = 'validation-icon';
        input.parentNode.appendChild(icon);
    }

    addInputHighlight(input) {
        const highlight = document.createElement('div');
        highlight.className = 'input-highlight';
        input.parentNode.appendChild(highlight);
    }

    validateField(field) {
        const value = field.value.trim();
        const fieldName = field.name;
        let isValid = true;
        let message = '';

        // Remove existing validation states
        this.clearFieldValidation(field);

        // Validation rules
        switch (fieldName) {
            case 'fullName':
                if (value.length < 2) {
                    isValid = false;
                    message = 'Full name must be at least 2 characters long';
                } else if (!/^[a-zA-Z\s]+$/.test(value)) {
                    isValid = false;
                    message = 'Full name can only contain letters and spaces';
                }
                break;

            case 'contactNumber':
                if (!/^[\d\s\-\+\(\)]+$/.test(value)) {
                    isValid = false;
                    message = 'Please enter a valid contact number';
                } else if (value.replace(/\D/g, '').length < 10) {
                    isValid = false;
                    message = 'Contact number must be at least 10 digits';
                }
                break;

            case 'email':
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    isValid = false;
                    message = 'Please enter a valid email address';
                }
                break;

            case 'address':
                if (value.length < 10) {
                    isValid = false;
                    message = 'Address must be at least 10 characters long';
                }
                break;

            case 'age':
                const age = parseInt(value);
                if (isNaN(age) || age < 1 || age > 120) {
                    isValid = false;
                    message = 'Please enter a valid age between 1 and 120';
                }
                break;

            case 'sex':
                if (!value) {
                    isValid = false;
                    message = 'Please select your sex';
                }
                break;
        }

        if (!isValid) {
            this.showFieldError(field, message);
        } else if (value) {
            this.showFieldSuccess(field);
        }
    }

    showFieldError(field, message) {
        const formGroup = field.closest('.form-group');
        formGroup.classList.add('error');
        
        const icon = formGroup.querySelector('.validation-icon');
        if (icon) {
            icon.className = 'validation-icon fas fa-times-circle';
        }

        this.showValidationMessage(formGroup, message, 'error');
    }

    showFieldSuccess(field) {
        const formGroup = field.closest('.form-group');
        formGroup.classList.add('success');
        
        const icon = formGroup.querySelector('.validation-icon');
        if (icon) {
            icon.className = 'validation-icon fas fa-check-circle';
        }
    }

    clearFieldValidation(field) {
        const formGroup = field.closest('.form-group');
        formGroup.classList.remove('success', 'error', 'warning');
        
        const icon = formGroup.querySelector('.validation-icon');
        if (icon) {
            icon.className = 'validation-icon';
        }

        const message = formGroup.querySelector('.validation-message');
        if (message) {
            message.remove();
        }
    }

    showValidationMessage(formGroup, message, type) {
        const existingMessage = formGroup.querySelector('.validation-message');
        if (existingMessage) {
            existingMessage.remove();
        }

        const messageDiv = document.createElement('div');
        messageDiv.className = `validation-message ${type}`;
        messageDiv.innerHTML = `
            <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'check-circle'}"></i>
            <span>${message}</span>
        `;
        
        formGroup.appendChild(messageDiv);
        setTimeout(() => messageDiv.classList.add('show'), 100);
    }

    async handleFormSubmission(e, form) {
            e.preventDefault();
            
        // Validate all fields
        const inputs = form.querySelectorAll('input, textarea, select');
        let isValid = true;

        inputs.forEach(input => {
            this.validateField(input);
            if (input.closest('.form-group').classList.contains('error')) {
                isValid = false;
            }
        });

        if (!isValid) {
            this.showToast('error', 'Validation Error', 'Please correct the errors in the form before submitting.');
            return false;
        }

        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            this.setButtonLoading(submitBtn, true);
        }

        this.showLoading('Generating your QR code...');

        // Simulate processing time for better UX
        setTimeout(() => {
            // Submit the form normally
            form.submit();
        }, 1500);
    }

    setButtonLoading(button, loading) {
        if (!button) return;

        if (loading) {
            button.classList.add('loading');
            button.disabled = true;
            
            const text = button.innerHTML;
            button.setAttribute('data-original-text', text);
            button.innerHTML = `
                <span class="btn-text" style="opacity: 0;">${text}</span>
                <div class="btn-spinner"></div>
            `;
        } else {
            button.classList.remove('loading');
            button.disabled = false;
            
            const originalText = button.getAttribute('data-original-text');
            if (originalText) {
                button.innerHTML = originalText;
            }
        }
    }

    initializeProgressIndicators() {
        const progressBars = document.querySelectorAll('.progress-bar');
        progressBars.forEach(bar => this.animateProgressBar(bar));
    }

    animateProgressBar(progressBar, targetProgress = 100) {
        const fill = progressBar.querySelector('.progress-fill');
        if (!fill) return;

        fill.style.width = '0%';
            setTimeout(() => {
            fill.style.width = targetProgress + '%';
        }, 100);
    }

    bindEvents() {
        // Navigation scroll effect
        window.addEventListener('scroll', () => {
            const navbar = document.querySelector('.navbar');
            if (navbar) {
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            }
        });

        // Mobile menu toggle
        const hamburger = document.querySelector('.hamburger');
        const navMenu = document.querySelector('.nav-menu');
        
        if (hamburger && navMenu) {
            hamburger.addEventListener('click', () => {
                navMenu.classList.toggle('active');
                hamburger.classList.toggle('active');
            });
        }

        // Close mobile menu when clicking outside
        document.addEventListener('click', (e) => {
            if (navMenu && navMenu.classList.contains('active')) {
                if (!e.target.closest('.navbar')) {
                    navMenu.classList.remove('active');
                    hamburger.classList.remove('active');
                }
            }
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }
}

// Enhanced QR Code Generation Experience
class QRCodeGenerator {
    constructor() {
        this.initializeQRPreview();
        this.initializeDownloadButtons();
    }

    initializeQRPreview() {
        const qrPreview = document.querySelector('.qr-preview');
        if (qrPreview) {
            this.setupQRPreview(qrPreview);
        }
    }

    setupQRPreview(qrPreview) {
        // Add placeholder if no QR code
        if (!qrPreview.querySelector('img')) {
            qrPreview.innerHTML = `
                <div class="qr-preview-placeholder">
                    <i class="fas fa-qrcode"></i>
                    <p>Your QR code will appear here after registration</p>
        </div>
    `;
        }
    }

    initializeDownloadButtons() {
        const downloadButtons = document.querySelectorAll('.download-btn');
        downloadButtons.forEach(btn => {
            btn.addEventListener('click', (e) => this.handleDownload(e, btn));
        });
    }

    async handleDownload(e, button) {
        e.preventDefault();
        
        const format = button.getAttribute('data-format');
        const href = button.getAttribute('href');
        
        // Show loading state
        this.setButtonLoading(button, true);
        
        try {
            // Simulate download processing
            await new Promise(resolve => setTimeout(resolve, 1500));
            
            // Trigger actual download
            window.location.href = href;
            
            // Show success message
            if (window.manifestLinkUX) {
                window.manifestLinkUX.showToast(
                    'success',
                    'Download Started',
                    `Your QR code is being downloaded in ${format.toUpperCase()} format.`,
                    3000
                );
            }
        } catch (error) {
            if (window.manifestLinkUX) {
                window.manifestLinkUX.showToast(
                    'error',
                    'Download Error',
                    'There was an error downloading your QR code. Please try again.',
                    5000
                );
            }
        } finally {
            // Reset button state after a delay
    setTimeout(() => {
                this.setButtonLoading(button, false);
            }, 2000);
        }
    }

    setButtonLoading(button, loading) {
        if (!button) return;

        if (loading) {
            button.classList.add('loading');
            button.style.pointerEvents = 'none';
        } else {
            button.classList.remove('loading');
            button.style.pointerEvents = 'auto';
        }
    }

    showQRGenerationAnimation() {
        const container = document.querySelector('.qr-generation-container');
        if (!container) return;

        // Add generation animation
        container.classList.add('generating');
        
        // Simulate QR generation process
    setTimeout(() => {
            container.classList.remove('generating');
            this.showSuccessAnimation();
    }, 3000);
}

    showSuccessAnimation() {
        const container = document.querySelector('.qr-display-content');
        if (!container) return;

        const successDiv = document.createElement('div');
        successDiv.className = 'success-animation';
        successDiv.innerHTML = `
            <div class="success-checkmark">
                <i class="fas fa-check"></i>
            </div>
        `;

        container.insertBefore(successDiv, container.firstChild);

        // Remove after animation
        setTimeout(() => {
            if (successDiv.parentNode) {
                successDiv.parentNode.removeChild(successDiv);
            }
        }, 3000);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.manifestLinkUX = new ManifestLinkUX();
    window.qrGenerator = new QRCodeGenerator();

    // Show welcome toast on first visit
    if (!localStorage.getItem('manifestlink_visited')) {
        setTimeout(() => {
            window.manifestLinkUX.showToast(
                'success',
                'Welcome to ManifestLink!',
                'Your QR-enabled passenger manifest system for Guimaras Port.',
                6000
            );
        }, 1000);
        localStorage.setItem('manifestlink_visited', 'true');
    }
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { ManifestLinkUX, QRCodeGenerator };
} 