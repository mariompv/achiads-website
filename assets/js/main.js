/**
 * ACHIADS - JavaScript Principal
 * Asociación Chilena de IA para el Desarrollo Sustentable
 * Versión: 1.2.0 - Optimizado para rendimiento
 */

/* ================================
   INICIALIZACIÓN
   ================================ */
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar componentes críticos primero
    initializeNavigation();
    initializeMobileMenu();
    
    // Inicializar componentes no críticos después
    requestIdleCallback(() => {
        initializeSmoothScrolling();
        initializeContactForm();
        initializeIntersectionObserver();
        initializeThemeDetection();
        initializePerformanceOptimizations();
    });
    
    console.log('✅ ACHIADS website initialized successfully');
});

// Cache DOM elements
const domCache = {
    nav: null,
    header: null,
    mobileToggle: null,
    contactForm: null
};

// Initialize DOM cache
function initializeDomCache() {
    domCache.nav = document.querySelector('.nav');
    domCache.header = document.querySelector('.header');
    domCache.mobileToggle = document.querySelector('.mobile-menu-toggle');
    domCache.contactForm = document.getElementById('contactForm');
}

/* ================================
   NAVEGACIÓN Y MENÚ
   ================================ */
function initializeNavigation() {
    const nav = document.querySelector('.nav');
    const header = document.querySelector('.header');
    let lastScrollY = window.scrollY;

    // Resaltar enlace activo basado en la sección visible
    function updateActiveNavLink() {
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.nav__link');

        let currentSection = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop - 100;
            const sectionHeight = section.offsetHeight;
            
            if (window.scrollY >= sectionTop && window.scrollY < sectionTop + sectionHeight) {
                currentSection = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${currentSection}`) {
                link.classList.add('active');
            }
        });
    }

    // Header con scroll inteligente
    function handleScroll() {
        const currentScrollY = window.scrollY;
        
        if (currentScrollY > lastScrollY && currentScrollY > 100) {
            // Scrolling down - hide header
            header.style.transform = 'translateY(-100%)';
        } else {
            // Scrolling up - show header
            header.style.transform = 'translateY(0)';
        }
        
        lastScrollY = currentScrollY;
        updateActiveNavLink();
    }

    window.addEventListener('scroll', throttle(handleScroll, 10));
    
    // Resaltar enlace activo al cargar
    updateActiveNavLink();
}

function initializeMobileMenu() {
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    const nav = document.querySelector('.nav');
    let isMenuOpen = false;

    if (mobileToggle && nav) {
        mobileToggle.addEventListener('click', function() {
            isMenuOpen = !isMenuOpen;
            
            // Toggle menu visibility
            nav.style.display = isMenuOpen ? 'block' : 'none';
            nav.style.position = isMenuOpen ? 'absolute' : 'static';
            nav.style.top = isMenuOpen ? '100%' : 'auto';
            nav.style.left = isMenuOpen ? '0' : 'auto';
            nav.style.right = isMenuOpen ? '0' : 'auto';
            nav.style.background = isMenuOpen ? 'var(--white)' : 'transparent';
            nav.style.boxShadow = isMenuOpen ? 'var(--shadow-lg)' : 'none';
            nav.style.zIndex = isMenuOpen ? '1001' : 'auto';
            nav.style.padding = isMenuOpen ? 'var(--spacing-lg)' : '0';

            // Update aria-expanded
            mobileToggle.setAttribute('aria-expanded', isMenuOpen);
            
            // Animate hamburger icon
            const icon = mobileToggle.querySelector('svg');
            if (icon) {
                icon.style.transform = isMenuOpen ? 'rotate(90deg)' : 'rotate(0deg)';
            }

            // Close menu when clicking nav links
            if (isMenuOpen) {
                const navLinks = nav.querySelectorAll('.nav__link');
                navLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        isMenuOpen = false;
                        nav.style.display = 'none';
                        mobileToggle.setAttribute('aria-expanded', 'false');
                        if (icon) icon.style.transform = 'rotate(0deg)';
                    }, { once: true });
                });
            }
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (isMenuOpen && !nav.contains(e.target) && !mobileToggle.contains(e.target)) {
                isMenuOpen = false;
                nav.style.display = 'none';
                mobileToggle.setAttribute('aria-expanded', 'false');
                const icon = mobileToggle.querySelector('svg');
                if (icon) icon.style.transform = 'rotate(0deg)';
            }
        });
    }
}

/* ================================
   SMOOTH SCROLLING
   ================================ */
function initializeSmoothScrolling() {
    // Smooth scrolling para enlaces internos
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                const offsetTop = targetElement.offsetTop - 80; // Account for header height
                
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });

                // Update URL without triggering scroll
                if (history.pushState) {
                    history.pushState(null, null, '#' + targetId);
                }
            }
        });
    });
}

/* ================================
   FORMULARIO DE CONTACTO
   ================================ */
function initializeContactForm() {
    const contactForm = document.getElementById('contactForm');
    
    if (!contactForm) return;

    // Validación en tiempo real
    const inputs = contactForm.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        input.addEventListener('blur', validateField);
        input.addEventListener('input', clearFieldError);
    });

    // Envío del formulario
    contactForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (!validateForm()) {
            showNotification('Por favor corrige los errores antes de enviar', 'error');
            return;
        }

        const submitButton = this.querySelector('button[type="submit"]');
        const originalText = submitButton.textContent;
        
        try {
            // Deshabilitar botón y mostrar loading
            submitButton.disabled = true;
            submitButton.textContent = 'Enviando...';
            submitButton.style.opacity = '0.7';

            const formData = new FormData(this);
            
            const response = await fetch('contact_form.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                showNotification('¡Mensaje enviado correctamente! Te responderemos pronto.', 'success');
                contactForm.reset();
                
                // Tracking para analytics
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'form_submit', {
                        'event_category': 'Contact',
                        'event_label': 'Contact Form'
                    });
                }
            } else {
                throw new Error(result.message || 'Error al enviar el mensaje');
            }

        } catch (error) {
            console.error('Error submitting form:', error);
            showNotification(error.message || 'Error al enviar el mensaje. Inténtalo de nuevo.', 'error');
        } finally {
            // Restaurar botón
            submitButton.disabled = false;
            submitButton.textContent = originalText;
            submitButton.style.opacity = '1';
        }
    });
}

function validateForm() {
    const form = document.getElementById('contactForm');
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!validateField({ target: input })) {
            isValid = false;
        }
    });

    return isValid;
}

function validateField(e) {
    const field = e.target;
    const value = field.value.trim();
    let isValid = true;
    let errorMessage = '';

    // Limpiar errores previos
    clearFieldError(e);

    // Validaciones específicas por tipo
    switch (field.type) {
        case 'email':
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (value && !emailRegex.test(value)) {
                errorMessage = 'Ingresa un email válido';
                isValid = false;
            }
            break;
        
        case 'text':
            if (field.hasAttribute('required') && value.length < 2) {
                errorMessage = 'Este campo debe tener al menos 2 caracteres';
                isValid = false;
            }
            break;
        
        case 'textarea':
            if (field.hasAttribute('required') && value.length < 10) {
                errorMessage = 'El mensaje debe tener al menos 10 caracteres';
                isValid = false;
            }
            break;
    }

    // Mostrar error si existe
    if (!isValid) {
        showFieldError(field, errorMessage);
    }

    return isValid;
}

function showFieldError(field, message) {
    field.style.borderColor = 'var(--secondary)';
    
    // Crear o actualizar mensaje de error
    let errorElement = field.parentNode.querySelector('.error-message');
    if (!errorElement) {
        errorElement = document.createElement('div');
        errorElement.className = 'error-message';
        errorElement.style.cssText = `
            color: var(--secondary);
            font-size: 0.75rem;
            margin-top: 0.25rem;
            display: block;
        `;
        field.parentNode.appendChild(errorElement);
    }
    errorElement.textContent = message;
}

function clearFieldError(e) {
    const field = e.target;
    field.style.borderColor = '';
    
    const errorElement = field.parentNode.querySelector('.error-message');
    if (errorElement) {
        errorElement.remove();
    }
}

/* ================================
   INTERSECTION OBSERVER (ANIMACIONES)
   ================================ */
function initializeIntersectionObserver() {
    // Configurar animaciones cuando los elementos entran en viewport
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in-up');
                
                // Animar contadores si los hay
                if (entry.target.classList.contains('stat__number')) {
                    animateCounter(entry.target);
                }
                
                // Animar barras de progreso si las hay
                if (entry.target.classList.contains('progress-bar')) {
                    animateProgressBar(entry.target);
                }
                
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observar elementos que deben animarse
    document.querySelectorAll('.card, .stat, .team-member').forEach(el => {
        observer.observe(el);
    });
}

function animateCounter(element) {
    const target = parseInt(element.textContent);
    const duration = 2000;
    const step = target / (duration / 16);
    let current = 0;

    const timer = setInterval(() => {
        current += step;
        if (current >= target) {
            element.textContent = target + (element.textContent.includes('+') ? '+' : '');
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current) + (element.textContent.includes('+') ? '+' : '');
        }
    }, 16);
}

/* ================================
   NOTIFICACIONES
   ================================ */
function showNotification(message, type = 'info', duration = 4000) {
    // Crear elemento de notificación
    const notification = document.createElement('div');
    notification.className = `notification notification--${type}`;
    
    const colors = {
        success: 'var(--primary)',
        error: 'var(--secondary)',
        info: 'var(--accent)',
        warning: '#FFA500'
    };

    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${colors[type] || colors.info};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: var(--shadow-lg);
        z-index: 3000;
        max-width: 400px;
        word-wrap: break-word;
        transform: translateX(100%);
        transition: transform 0.3s ease-out;
        font-weight: 500;
    `;

    // Icono según tipo
    const icons = {
        success: '✅',
        error: '❌',
        info: 'ℹ️',
        warning: '⚠️'
    };

    notification.innerHTML = `
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span>${icons[type] || icons.info}</span>
            <span>${message}</span>
        </div>
    `;

    document.body.appendChild(notification);

    // Animar entrada
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 10);

    // Auto-remover
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, duration);

    // Permitir cerrar manualmente
    notification.addEventListener('click', () => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    });
}

/* ================================
   DETECCIÓN DE TEMA Y PREFERENCIAS
   ================================ */
function initializeThemeDetection() {
    // Detectar preferencia de movimiento reducido
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    
    if (prefersReducedMotion) {
        document.documentElement.style.setProperty('--transition-fast', '0s');
        document.documentElement.style.setProperty('--transition-normal', '0s');
        document.documentElement.style.setProperty('--transition-slow', '0s');
    }

    // Detectar tema oscuro del sistema (para futuras implementaciones)
    const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    if (prefersDarkScheme) {
        // Preparado para modo oscuro futuro
        document.documentElement.setAttribute('data-theme', 'dark');
    }
}

/* ================================
   OPTIMIZACIONES DE RENDIMIENTO
   ================================ */
function initializePerformanceOptimizations() {
    // Lazy load images
    const lazyImages = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                observer.unobserve(img);
            }
        });
    });

    lazyImages.forEach(img => imageObserver.observe(img));

    // Preload critical resources
    preloadCriticalResources();
    
    // Initialize DOM cache
    initializeDomCache();
}

function preloadCriticalResources() {
    const criticalResources = [
        '/assets/css/styles.css',
        '/assets/images/logo.svg',
        '/favicon.svg'
    ];

    criticalResources.forEach(resource => {
        const link = document.createElement('link');
        link.rel = 'preload';
        link.as = resource.endsWith('.css') ? 'style' : 'image';
        link.href = resource;
        document.head.appendChild(link);
    });
}

/* ================================
   UTILIDADES
   ================================ */
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
    };
}

function debounce(func, wait, immediate) {
    let timeout;
    return function() {
        const context = this, args = arguments;
        const later = function() {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
}

/* ================================
   ANALYTICS Y TRACKING
   ================================ */
function trackUserInteraction(action, category = 'User Interaction', label = '') {
    if (typeof gtag !== 'undefined') {
        gtag('event', action, {
            'event_category': category,
            'event_label': label
        });
    }
}

// Tracking automático de clics en CTAs
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('cta-button')) {
        const buttonText = e.target.textContent.trim();
        trackUserInteraction('cta_click', 'CTA', buttonText);
    }
});

/* ================================
   FUNCIONALIDADES ESPECÍFICAS DE COMUNIDAD
   ================================ */
// Funciones de utilidad para la página de comunidad
window.CommunityUtils = {
    // Función para compartir contenido
    shareContent: function(title, text, url) {
        if (navigator.share) {
            navigator.share({
                title: title,
                text: text,
                url: url
            }).catch(console.error);
        } else {
            // Fallback: copiar al portapapeles
            navigator.clipboard.writeText(url).then(() => {
                showNotification('Enlace copiado al portapapeles', 'success');
            });
        }
    },

    // Función para validar URLs de video
    validateVideoUrl: function(url) {
        const youtubeRegex = /^(https?:\/\/)?(www\.)?(youtube\.com\/watch\?v=|youtu\.be\/)[\w\-]+/;
        const vimeoRegex = /^(https?:\/\/)?(www\.)?vimeo\.com\/\d+/;
        return youtubeRegex.test(url) || vimeoRegex.test(url);
    },

    // Función para generar ID único
    generateId: function() {
        return Date.now() + Math.random().toString(36).substr(2, 9);
    },

    // Función para formatear texto con markdown básico
    formatText: function(text) {
        return text
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/\n/g, '<br>');
    }
};

/* ================================
   SERVICE WORKER
   ================================ */
// Registrar service worker si está disponible
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => {
                console.log('SW registered: ', registration);
                
                // Actualizar cache cuando hay nueva versión
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            showNotification('Nueva versión disponible. Recarga la página para actualizar.', 'info', 8000);
                        }
                    });
                });
            })
            .catch(registrationError => {
                console.log('SW registration failed: ', registrationError);
            });
    });
}

/* ================================
   ERROR HANDLING GLOBAL
   ================================ */
window.addEventListener('error', function(e) {
    console.error('Error global capturado:', e.error);
    
    // Reportar errores críticos a analytics (opcional)
    if (typeof gtag !== 'undefined') {
        gtag('event', 'exception', {
            'description': e.error?.message || 'Unknown error',
            'fatal': false
        });
    }
});

window.addEventListener('unhandledrejection', function(e) {
    console.error('Promise rechazado no manejado:', e.reason);
});

console.log('🚀 ACHIADS JavaScript loaded successfully');