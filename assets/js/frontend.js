/**
 * BeaconCMS — Frontend JavaScript
 * Beacon Hospital Public Site
 */
(function () {
    'use strict';

    /* ========== Navbar Scroll Behaviour ========== */
    const navbar = document.getElementById('main-navbar');

    function handleNavbarScroll() {
        if (!navbar) return;
        if (window.scrollY > 60) {
            navbar.classList.add('navbar--solid');
        } else {
            navbar.classList.remove('navbar--solid');
        }
    }

    // If the page has no hero section force navbar solid immediately
    function initNavbar() {
        if (!navbar) return;
        const hero = document.querySelector('.hero');
        if (!hero) {
            navbar.classList.add('navbar--solid');
        } else {
            window.addEventListener('scroll', handleNavbarScroll, { passive: true });
            handleNavbarScroll();
        }
    }

    /* ========== Mobile Menu Toggle ========== */
    function initMobileMenu() {
        const hamburger = document.getElementById('hamburger-btn');
        const navMenu = document.getElementById('nav-menu');
        const overlay = document.getElementById('mobile-overlay');
        if (!hamburger || !navMenu) return;

        function toggleMenu() {
            const isOpen = navMenu.classList.toggle('open');
            hamburger.classList.toggle('active', isOpen);
            if (overlay) overlay.classList.toggle('active', isOpen);
            document.body.style.overflow = isOpen ? 'hidden' : '';
        }

        function closeMenu() {
            navMenu.classList.remove('open');
            hamburger.classList.remove('active');
            if (overlay) overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        hamburger.addEventListener('click', toggleMenu);
        if (overlay) overlay.addEventListener('click', closeMenu);

        // Close on nav link click (mobile)
        navMenu.querySelectorAll('.navbar__link').forEach(function (link) {
            link.addEventListener('click', closeMenu);
        });

        // Close on Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeMenu();
        });
    }

    /* ========== Scroll-Reveal (Intersection Observer) ========== */
    function initScrollReveal() {
        var revealElements = document.querySelectorAll('.reveal');
        if (!revealElements.length) return;

        if (!('IntersectionObserver' in window)) {
            // Fallback: show everything
            revealElements.forEach(function (el) {
                el.classList.add('reveal--visible');
            });
            return;
        }

        var observer = new IntersectionObserver(
            function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('reveal--visible');
                        observer.unobserve(entry.target);
                    }
                });
            },
            {
                threshold: 0.12,
                rootMargin: '0px 0px -40px 0px'
            }
        );

        revealElements.forEach(function (el) {
            observer.observe(el);
        });
    }

    /* ========== Smooth Scroll for Anchor Links ========== */
    function initSmoothScroll() {
        document.addEventListener('click', function (e) {
            var link = e.target.closest('a[href^="#"]');
            if (!link) return;
            var targetId = link.getAttribute('href');
            if (targetId === '#') return;
            var target = document.querySelector(targetId);
            if (!target) return;
            e.preventDefault();
            var offset = navbar ? navbar.offsetHeight : 0;
            var top = target.getBoundingClientRect().top + window.pageYOffset - offset - 16;
            window.scrollTo({ top: top, behavior: 'smooth' });
        });
    }

    /* ========== Specialty Filter (Doctors Page) ========== */
    function initSpecialtyFilter() {
        var filterSelect = document.getElementById('specialty-filter');
        if (!filterSelect) return;

        filterSelect.addEventListener('change', function () {
            var selectedId = this.value;
            var cards = document.querySelectorAll('[data-specialty-id]');
            cards.forEach(function (card) {
                if (!selectedId || card.getAttribute('data-specialty-id') === selectedId) {
                    card.style.display = '';
                    // Re-trigger reveal animation
                    card.classList.remove('reveal--visible');
                    void card.offsetWidth; // Force reflow
                    card.classList.add('reveal--visible');
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }

    /* ========== Lazy Loading Images ========== */
    function initLazyLoad() {
        var lazyImages = document.querySelectorAll('img[data-src]');
        if (!lazyImages.length) return;

        if (!('IntersectionObserver' in window)) {
            lazyImages.forEach(function (img) {
                img.src = img.getAttribute('data-src');
                img.removeAttribute('data-src');
                img.classList.add('loaded');
            });
            return;
        }

        var imgObserver = new IntersectionObserver(
            function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        var img = entry.target;
                        img.src = img.getAttribute('data-src');
                        img.removeAttribute('data-src');
                        img.onload = function () {
                            img.classList.add('loaded');
                        };
                        imgObserver.unobserve(img);
                    }
                });
            },
            {
                rootMargin: '100px'
            }
        );

        lazyImages.forEach(function (img) {
            imgObserver.observe(img);
        });
    }

    /* ========== Profile Tabs (Consultant Single Page) ========== */
    function initProfileTabs() {
        var tabs = document.querySelectorAll('.profile-tab');
        var panels = document.querySelectorAll('.profile-panel');
        if (!tabs.length || !panels.length) return;

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                var targetPanel = this.getAttribute('data-tab');

                tabs.forEach(function (t) {
                    t.classList.remove('profile-tab--active');
                });
                panels.forEach(function (p) {
                    p.classList.remove('profile-panel--active');
                });

                this.classList.add('profile-tab--active');
                var panel = document.getElementById(targetPanel);
                if (panel) panel.classList.add('profile-panel--active');
            });
        });
    }

    /* ========== Initialize Everything ========== */
    function init() {
        initNavbar();
        initMobileMenu();
        initScrollReveal();
        initSmoothScroll();
        initSpecialtyFilter();
        initLazyLoad();
        initProfileTabs();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
