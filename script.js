// Loading Screen Animation - Working version with 2 second display
console.log('Script starting...');

// Initialize hamburger menu immediately on DOM load (fix for index.html)
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM Content Loaded');
    
    // Initialize hamburger menu immediately for all pages
    initHamburgerMenu();
    
    const loadingScreen = document.getElementById('loading-screen');
    console.log('Loading screen element:', loadingScreen);
    
    if (loadingScreen) {
        console.log('Starting 2 second timer...');
        // Show badge for exactly 2 seconds
        setTimeout(() => {
            console.log('2 seconds passed, adding hide class...');
            // Add the hide class to trigger CSS transition
            loadingScreen.classList.add('hide');
            
            // After zoom completes, hide loading screen completely
            setTimeout(() => {
                console.log('Transition complete, hiding loading screen...');
                loadingScreen.style.display = 'none';
                initPageAnimations();
            }, 1500); // Wait for CSS transition to complete
        }, 2000); // Show logo for 2 seconds
    } else {
        console.error('Loading screen element not found!');
        // Fallback if loading screen not found
        initPageAnimations();
    }
});

// Backup event listener for window load
window.addEventListener('load', () => {
    console.log('Window loaded');
    const loadingScreen = document.getElementById('loading-screen');
    
    // Only run if the loading screen is still visible (DOMContentLoaded didn't work)
    if (loadingScreen && !loadingScreen.classList.contains('hide')) {
        console.log('Backup timer starting...');
        setTimeout(() => {
            console.log('Backup: adding hide class...');
            loadingScreen.classList.add('hide');
            
            setTimeout(() => {
                console.log('Backup: hiding loading screen...');
                loadingScreen.style.display = 'none';
                initPageAnimations();
            }, 1500);
        }, 2000);
    }
});

// Separate hamburger menu initialization function
function initHamburgerMenu() {
    console.log('Initializing hamburger menu...');
    
    // Mobile menu toggle
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');

    if (hamburger && navMenu) {
        console.log('Hamburger menu elements found, setting up event listeners...');
        
        // Remove any existing event listeners to prevent duplicates
        hamburger.removeEventListener('click', handleHamburgerClick);
        
        // Add click event listener
        hamburger.addEventListener('click', handleHamburgerClick);

        // Close mobile menu when clicking a link
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
            });
        });
    } else {
        console.log('Hamburger menu elements not found');
    }
}

// Hamburger click handler function
function handleHamburgerClick(e) {
    e.preventDefault();
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');
    
    if (hamburger && navMenu) {
        hamburger.classList.toggle('active');
        navMenu.classList.toggle('active');
        console.log('Hamburger menu toggled');
    }
}

// Initialize page animations
function initPageAnimations() {
    // Smooth scroll behavior for internal links only
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', (e) => {
            const href = link.getAttribute('href');
            
            // Only prevent default and use smooth scroll for internal anchor links (starting with #)
            if (href.startsWith('#')) {
                e.preventDefault();
                const targetSection = document.querySelector(href);
                
                if (targetSection) {
                    targetSection.scrollIntoView({ behavior: 'smooth' });
                }
            }
            // For external links (like about.html, recruitment.html), allow normal navigation
            // No preventDefault() needed - browser will handle normally
        });
    });

    // Initialize hamburger menu (in case it wasn't initialized earlier)
    initHamburgerMenu();

    // Cards stagger animation
    animateCards();

    // Form handling
    initContactForm();

    // Initialize landing phase transition
    initLandingTransition();
    
    // Initialize triangle sponsor carousel
    initTriangleCarousel();
    
    // Initialize recruitment page functionality
    initRecruitmentPage();
    
    // Initialize tournament image modal functionality
    initTournamentImageModal();

    // Initialize academy image modal functionality
    initAcademyImageModal();
    
    // Initialize registration page dynamic height functionality
    initRegistrationPage();
}

// Initialize academy image modal functionality
function initAcademyImageModal() {
    // Only run on academy page
    if (!document.querySelector('.academy-structure-section')) return;

    const academyImageContainer = document.querySelector('.academy-structure-image');
    let imageModal = null;

    // Create modal HTML structure
    function createModal() {
        const modal = document.createElement('div');
        modal.className = 'image-modal';
        modal.innerHTML = `
            <div class="modal-image-container">
                <span class="close-image-modal">&times;</span>
                <img class="modal-image" src="" alt="">
            </div>
        `;
        document.body.appendChild(modal);
        return modal;
    }

    // Open modal with image
    function openModal(imageSrc, imageAlt) {
        if (!imageModal) {
            imageModal = createModal();
        }

        const modalImage = imageModal.querySelector('.modal-image');
        modalImage.src = imageSrc;
        modalImage.alt = imageAlt;

        imageModal.classList.add('show');
        document.body.classList.add('modal-open');

        // Add event listeners for closing
        const closeBtn = imageModal.querySelector('.close-image-modal');
        closeBtn.addEventListener('click', closeModal);

        // Close on background click
        imageModal.addEventListener('click', (e) => {
            if (e.target === imageModal) {
                closeModal();
            }
        });

        // Close on ESC key
        document.addEventListener('keydown', handleEscKey);
    }

    // Close modal
    function closeModal() {
        if (imageModal) {
            imageModal.classList.remove('show');
            document.body.classList.remove('modal-open');

            // Remove ESC key listener
            document.removeEventListener('keydown', handleEscKey);

            // Clean up modal after animation
            setTimeout(() => {
                if (imageModal && imageModal.parentNode) {
                    imageModal.parentNode.removeChild(imageModal);
                    imageModal = null;
                }
            }, 300);
        }
    }

    // Handle ESC key press
    function handleEscKey(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    }

    // Function to add event listeners for fullscreen modal on mobile only
    function addFullscreenListeners() {
        if (!academyImageContainer) return;

        academyImageContainer.addEventListener('click', (e) => {
            e.preventDefault();
            const img = academyImageContainer.querySelector('img');
            if (img) {
                openModal(img.src, img.alt);
            }
        });

        academyImageContainer.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const img = academyImageContainer.querySelector('img');
                if (img) {
                    openModal(img.src, img.alt);
                }
            }
        });

        academyImageContainer.setAttribute('tabindex', '0');
        academyImageContainer.setAttribute('role', 'button');
        academyImageContainer.setAttribute('aria-label', 'View image in fullscreen');
    }

    // Function to remove event listeners
    function removeFullscreenListeners() {
        if (!academyImageContainer) return;

        academyImageContainer.removeEventListener('click', openModal);
        academyImageContainer.removeEventListener('keydown', openModal);
        academyImageContainer.removeAttribute('tabindex');
        academyImageContainer.removeAttribute('role');
        academyImageContainer.removeAttribute('aria-label');
    }

    // Media query to detect mobile devices
    const mobileMediaQuery = window.matchMedia('(max-width: 768px)');

    // Function to handle media query changes
    function handleMediaChange(e) {
        if (e.matches) {
            addFullscreenListeners();
        } else {
            removeFullscreenListeners();
        }
    }

    // Initial check
    if (mobileMediaQuery.matches) {
        addFullscreenListeners();
    }

    // Listen for changes
    mobileMediaQuery.addListener(handleMediaChange);
}

// Animate cards with stagger effect
function animateCards() {
    const cards = document.querySelectorAll('.card');
    
    cards.forEach((card, index) => {
        setTimeout(() => {
            card.style.animationDelay = `${index * 0.2}s`;
        }, 100);
    });
}

// 3-Sponsor Horizontal Carousel
function initTriangleCarousel() {
    const sponsorSlides = document.querySelectorAll('.sponsor-slide');
    const progressDots = document.querySelectorAll('.progress-dot');
    
    if (sponsorSlides.length === 0) return;
    
    let currentIndex = 0;
    const totalSlides = sponsorSlides.length;
    let autoRotateInterval;
    
    function updateCarousel() {
        // Remove all positioning classes from all slides
        sponsorSlides.forEach(slide => {
            slide.classList.remove('focal', 'side-left', 'side-right');
        });
        
        // Calculate positions for 3-sponsor view
        // Current focal sponsor (center)
        sponsorSlides[currentIndex].classList.add('focal');
        
        // Left side sponsor (previous in sequence)
        const leftIndex = (currentIndex - 1 + totalSlides) % totalSlides;
        sponsorSlides[leftIndex].classList.add('side-left');
        
        // Right side sponsor (next in sequence)
        const rightIndex = (currentIndex + 1) % totalSlides;
        sponsorSlides[rightIndex].classList.add('side-right');
        
        // Update progress dots
        progressDots.forEach((dot, index) => {
            dot.classList.toggle('active', index === currentIndex);
        });
    }
    
    function nextSponsor() {
        currentIndex = (currentIndex + 1) % totalSlides;
        updateCarousel();
    }
    
    function startAutoRotate() {
        if (autoRotateInterval) clearInterval(autoRotateInterval);
        // Continuous infinite loop - always moving to the right
        autoRotateInterval = setInterval(nextSponsor, 5000); // 5 seconds per sponsor
    }
    
    function stopAutoRotate() {
        if (autoRotateInterval) clearInterval(autoRotateInterval);
    }
    
    // Optional: Allow manual control by clicking on progress dots
    progressDots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            stopAutoRotate();
            currentIndex = index;
            updateCarousel();
            startAutoRotate();
        });
    });
    
    // Optional: Pause on hover over the carousel area
    const sponsorsContainer = document.querySelector('.sponsors-container');
    if (sponsorsContainer) {
        sponsorsContainer.addEventListener('mouseenter', stopAutoRotate);
        sponsorsContainer.addEventListener('mouseleave', startAutoRotate);
    }
    
    // Initialize carousel
    updateCarousel();
    
    // Start auto-rotation after a brief delay to let animations settle
    setTimeout(() => {
        startAutoRotate();
    }, 1000);
}

// Recruitment page functionality
function initRecruitmentPage() {
    // Only run on recruitment page
    if (!document.querySelector('.recruitment-container')) return;
    
    const testimonialCards = document.querySelectorAll('.testimonial-card');
    const videoModal = document.getElementById('videoModal');
    const closeModal = document.querySelector('.close-modal');
    const modalPlayerName = document.getElementById('modalPlayerName');
    const modalPlayerPosition = document.getElementById('modalPlayerPosition');
    const modalTestimonialText = document.getElementById('modalTestimonialText');
    const modalVideo = document.getElementById('modalVideo');
    const modalVideoSource = document.getElementById('modalVideoSource');
    
    // Testimonial data
    const testimonialData = {
        1: {
            name: "Blaine Mabie",
            position: "",
            testimonial: "",
            videoSrc: "Blaine-video.mp4"
        },
        2: {
            name: "Phoenix Coursey",
            position: "",
            testimonial: "",
            videoSrc: "Phoenix.mp4"
        },
        3: {
            name: "Louis Wickremesekera",
            position: "",
            testimonial: "",
            videoSrc: "Louis.mp4"
        },
        4: {
            name: "Edy Belingher",
            position: "",
            testimonial: "",
            videoSrc: "Edy-video.mp4"
        },
        5: {
            name: "David Park",
            position: "Winger - Former Player",
            testimonial: "Wanaka FC gave me the foundation for my professional career. The skills, discipline, and values I learned here have stayed with me throughout my journey. Even after moving on to professional football, I still consider Wanaka FC my home club.",
            videoSrc: "david-park.mp4"
        },
        6: {
            name: "Lisa Anderson",
            position: "Captain - Senior Women's Team",
            testimonial: "The club culture here is amazing. We're not just teammates, we're family. The leadership opportunities, the competitive environment, and the support from the entire club community make Wanaka FC a special place to play football.",
            videoSrc: "lisa-anderson.mp4"
        }
    };
    
    // Add click event listeners to testimonial cards
    testimonialCards.forEach(card => {
        card.addEventListener('click', () => {
            const videoId = card.getAttribute('data-video');
            const data = testimonialData[videoId];
            
            if (data) {
                openVideoModal(data);
            }
        });
    });
    
    // Close modal functionality
    if (closeModal) {
        closeModal.addEventListener('click', () => {
            closeVideoModal();
        });
    }
    
    // Close modal when clicking outside
    window.addEventListener('click', (event) => {
        if (event.target === videoModal) {
            closeVideoModal();
        }
    });
    
    // Handle recruitment form submission
    const recruitmentForm = document.querySelector('.recruitment-form');
    if (recruitmentForm) {
        recruitmentForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const formData = new FormData(recruitmentForm);
            const data = Object.fromEntries(formData);
            
            // Basic validation
            if (!data.firstName || !data.lastName || !data.email || !data.phone || 
                !data.age || !data.position || !data.experience || !data.motivation) {
                alert('Please fill in all required fields.');
                return;
            }
            
            const submitBtn = recruitmentForm.querySelector('.submit-btn');
            const originalText = submitBtn.textContent;
            
            submitBtn.textContent = 'Submitting...';
            submitBtn.disabled = true;
            
            // Simulate form submission
            setTimeout(() => {
                alert('Thank you for your application! We\'ll review your information and get back to you within 48 hours.');
                recruitmentForm.reset();
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }, 2000);
        });
    }
    
    // Open video modal
    function openVideoModal(data) {
        modalPlayerName.textContent = data.name;
        modalPlayerPosition.textContent = data.position;
        modalTestimonialText.textContent = data.testimonial;
        
        // Set video source
        modalVideoSource.src = data.videoSrc;
        modalVideo.load(); // Reload video with new source
        
        // Show loading state
        showVideoLoading();
        
        // Handle video load events
        modalVideo.addEventListener('loadeddata', () => {
            hideVideoLoading();
            modalVideo.style.display = 'block';
        });
        
        modalVideo.addEventListener('error', () => {
            showVideoError();
        });
        
        videoModal.style.display = 'block';
    }
    
    // Close video modal
    function closeVideoModal() {
        videoModal.style.display = 'none';
        
        // Pause and reset video
        if (modalVideo) {
            modalVideo.pause();
            modalVideo.currentTime = 0;
            modalVideo.style.display = 'none';
        }
        
        // Hide loading/error states
        hideVideoLoading();
        hideVideoError();
    }
    
    // Show video loading state
    function showVideoLoading() {
        const videoContainer = document.querySelector('.video-container');
        let loadingDiv = videoContainer.querySelector('.video-loading');
        
        if (!loadingDiv) {
            loadingDiv = document.createElement('div');
            loadingDiv.className = 'video-loading';
            loadingDiv.innerHTML = '<i class="fas fa-spinner"></i>';
            videoContainer.insertBefore(loadingDiv, videoContainer.firstChild);
        }
        
        loadingDiv.style.display = 'flex';
        modalVideo.style.display = 'none';
    }
    
    // Hide video loading state
    function hideVideoLoading() {
        const loadingDiv = document.querySelector('.video-loading');
        if (loadingDiv) {
            loadingDiv.style.display = 'none';
        }
    }
    
    // Show video error state
    function showVideoError() {
        const videoContainer = document.querySelector('.video-container');
        let errorDiv = videoContainer.querySelector('.video-error');
        
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'video-error';
            errorDiv.innerHTML = `
                <i class="fas fa-exclamation-triangle"></i>
                <p>Video could not be loaded. Please check that the video file exists.</p>
            `;
            videoContainer.insertBefore(errorDiv, videoContainer.firstChild);
        }
        
        errorDiv.style.display = 'flex';
        modalVideo.style.display = 'none';
        hideVideoLoading();
    }
    
    // Hide video error state
    function hideVideoError() {
        const errorDiv = document.querySelector('.video-error');
        if (errorDiv) {
            errorDiv.style.display = 'none';
        }
    }
}

// Contact form handling with SMTP2GO integration
function initContactForm() {
    const forms = document.querySelectorAll('.contact-form');
    
    // Only initialize if contact forms exist
    if (forms.length === 0) return;
    
    forms.forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            
            // Validate required fields
            if (!data.name || !data.email || !data.message) {
                showFormMessage(form, 'Please fill in all required fields.', 'error');
                return;
            }
            
            // Validate email format
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(data.email)) {
                showFormMessage(form, 'Please enter a valid email address.', 'error');
                return;
            }
            
            // Validate message length
            if (data.message.length < 10) {
                showFormMessage(form, 'Please enter a message with at least 10 characters.', 'error');
                return;
            }
            
            const submitBtn = form.querySelector('.submit-btn');
            const originalText = submitBtn.textContent;
            
            // Update button state
            submitBtn.textContent = 'Sending...';
            submitBtn.disabled = true;
            
            // Determine which page the form is on
            const currentPage = getCurrentPageName();
            
            // Prepare data for submission
            const submissionData = {
                name: data.name.trim(),
                email: data.email.trim(),
                message: data.message.trim(),
                page: currentPage
            };
            
            try {
                // Send form data to PHP handler
                const response = await fetch('send-email.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(submissionData)
                });
                
                // Check if response is ok
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    // Log the actual response for debugging
                    const responseText = await response.text();
                    console.error('Non-JSON response received:', responseText);
                    throw new Error('Server returned non-JSON response. Please check server configuration.');
                }
                
                const result = await response.json();
                
                // Validate JSON structure
                if (typeof result !== 'object' || result === null) {
                    throw new Error('Invalid response format from server');
                }
                
                if (result.success) {
                    showFormMessage(form, result.message, 'success');
                    form.reset();
                } else {
                    showFormMessage(form, result.message || 'Unknown error occurred', 'error');
                }
                
            } catch (error) {
                console.error('Form submission error:', error);
                
                // Provide more specific error messages based on error type
                let errorMessage = 'Sorry, there was an error sending your message. Please try again later or contact us directly at info@wanakafootball.nz';
                
                if (error.name === 'SyntaxError' && error.message.includes('JSON')) {
                    errorMessage = 'Server configuration error. Please contact us directly at info@wanakafootball.nz';
                    console.error('JSON parsing failed - server likely returned HTML error page');
                } else if (error.message.includes('HTTP error')) {
                    errorMessage = 'Server error occurred. Please try again or contact us directly at info@wanakafootball.nz';
                } else if (error.message.includes('Failed to fetch')) {
                    errorMessage = 'Network error. Please check your connection and try again.';
                }
                
                showFormMessage(form, errorMessage, 'error');
            } finally {
                // Reset button state
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        });
    });
}

// Helper function to show form messages
function showFormMessage(form, message, type) {
    // Remove any existing message
    const existingMessage = form.querySelector('.form-message');
    if (existingMessage) {
        existingMessage.remove();
    }
    
    // Create new message element
    const messageDiv = document.createElement('div');
    messageDiv.className = `form-message form-message-${type}`;
    messageDiv.textContent = message;
    
    // Insert message before the submit button
    const submitBtn = form.querySelector('.submit-btn');
    form.insertBefore(messageDiv, submitBtn);
    
    // Auto-remove success messages after 5 seconds
    if (type === 'success') {
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.remove();
            }
        }, 5000);
    }
}

// Registration page dynamic height functionality
function initRegistrationPage() {
    // Only run on registration page
    if (!document.querySelector('.registration-page')) return;
    
    let iframe = null;
    let resizeObserver = null;
    let mutationObserver = null;
    let heightCheckInterval = null;
    const registrationSection = document.querySelector('.registration-page');
    
    // Function to find and monitor the iframe
    function findAndMonitorIframe() {
        iframe = document.querySelector('.registration-page iframe');
        
        if (iframe) {
            console.log('Registration iframe found, setting up dynamic height monitoring...');
            setupDynamicHeight();
        } else {
            // If iframe not found, check again after a short delay
            setTimeout(findAndMonitorIframe, 500);
        }
    }
    
    // Function to expand the registration section
    function expandRegistrationSection() {
        if (registrationSection && !registrationSection.classList.contains('form-expanded')) {
            registrationSection.classList.add('form-expanded');
            console.log('Registration section expanded');
        }
    }
    
    // Function to collapse the registration section
    function collapseRegistrationSection() {
        if (registrationSection && registrationSection.classList.contains('form-expanded')) {
            registrationSection.classList.remove('form-expanded');
            console.log('Registration section collapsed');
        }
    }
    
    // Function to setup dynamic height monitoring
    function setupDynamicHeight() {
        if (!iframe) return;
        
        // Function to check and update iframe height
        function checkIframeHeight() {
            try {
                // Try to access iframe content height
                if (iframe.contentDocument && iframe.contentDocument.body) {
                    const contentHeight = iframe.contentDocument.body.scrollHeight;
                    const currentMinHeight = parseInt(window.getComputedStyle(iframe).minHeight);
                    
                    // If content is significantly larger than current min-height, expand
                    if (contentHeight > currentMinHeight + 200) {
                        iframe.classList.add('expanded');
                        expandRegistrationSection();
                        console.log('Registration form expanded - height adjusted');
                    }
                    // If content is much smaller and form seems collapsed, shrink
                    else if (contentHeight < 600 && iframe.classList.contains('expanded')) {
                        iframe.classList.remove('expanded');
                        collapseRegistrationSection();
                        console.log('Registration form collapsed - height adjusted');
                    }
                }
            } catch (e) {
                // Cross-origin restrictions - use alternative method
                checkIframeHeightAlternative();
            }
        }
        
        // Alternative method when cross-origin restrictions apply
        function checkIframeHeightAlternative() {
            // Monitor for changes in iframe's natural height
            const iframeRect = iframe.getBoundingClientRect();
            
            // If iframe appears to be loading content (common behavior)
            if (iframeRect.height > window.innerHeight * 0.7) {
                if (!iframe.classList.contains('expanded')) {
                    iframe.classList.add('expanded');
                    expandRegistrationSection();
                    console.log('Registration form likely expanded - height adjusted (alternative method)');
                }
            } else if (iframeRect.height < window.innerHeight * 0.5 && iframe.classList.contains('expanded')) {
                iframe.classList.remove('expanded');
                collapseRegistrationSection();
                console.log('Registration form likely collapsed - height adjusted (alternative method)');
            }
        }
        
        // Enhanced detection for form interactions
        function detectFormInteraction() {
            // Check if there are any form elements or buttons that might indicate form expansion
            const registrationContainer = document.querySelector('.registration-page .about-simple > div');
            if (registrationContainer) {
                // Look for signs of form expansion
                const containerHeight = registrationContainer.getBoundingClientRect().height;
                if (containerHeight > window.innerHeight * 0.6) {
                    expandRegistrationSection();
                    iframe.classList.add('expanded');
                    console.log('Form expansion detected via container height');
                }
            }
        }
        
        // Set up ResizeObserver to monitor iframe size changes
        if (window.ResizeObserver) {
            resizeObserver = new ResizeObserver((entries) => {
                for (let entry of entries) {
                    const { height } = entry.contentRect;
                    
                    // If iframe height increases significantly, it's likely expanded
                    if (height > window.innerHeight * 0.7 && !iframe.classList.contains('expanded')) {
                        iframe.classList.add('expanded');
                        expandRegistrationSection();
                        console.log('Registration form expanded detected via ResizeObserver');
                    }
                    // If iframe height decreases significantly, it might be collapsed
                    else if (height < window.innerHeight * 0.5 && iframe.classList.contains('expanded')) {
                        iframe.classList.remove('expanded');
                        collapseRegistrationSection();
                        console.log('Registration form collapsed detected via ResizeObserver');
                    }
                }
            });
            
            resizeObserver.observe(iframe);
        }
        
        // Set up MutationObserver to watch for changes in iframe attributes and DOM
        if (window.MutationObserver) {
            mutationObserver = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.type === 'attributes' && 
                        (mutation.attributeName === 'style' || mutation.attributeName === 'height')) {
                        setTimeout(checkIframeHeight, 100);
                    }
                    // Also watch for child node changes that might indicate form loading
                    else if (mutation.type === 'childList') {
                        setTimeout(detectFormInteraction, 200);
                    }
                });
            });
            
            mutationObserver.observe(iframe, {
                attributes: true,
                attributeFilter: ['style', 'height', 'class'],
                childList: true,
                subtree: true
            });
        }
        
        // Periodic height check as fallback
        heightCheckInterval = setInterval(() => {
            checkIframeHeight();
            detectFormInteraction();
        }, 3000);
        
        // Initial height check after iframe loads
        iframe.addEventListener('load', () => {
            setTimeout(() => {
                checkIframeHeight();
                detectFormInteraction();
            }, 1500);
        });
        
        // Listen for postMessage events from iframe (if the form sends them)
        window.addEventListener('message', (event) => {
            // Check if message is from the registration form
            if (event.data && typeof event.data === 'object') {
                if (event.data.type === 'formExpanded' || event.data.height > window.innerHeight * 0.7) {
                    iframe.classList.add('expanded');
                    expandRegistrationSection();
                    console.log('Registration form expanded via postMessage');
                }
                else if (event.data.type === 'formCollapsed' || event.data.height < window.innerHeight * 0.5) {
                    iframe.classList.remove('expanded');
                    collapseRegistrationSection();
                    console.log('Registration form collapsed via postMessage');
                }
            }
        });
        
        // Monitor for user interactions that might trigger form expansion
        document.addEventListener('click', (e) => {
            // If click is within the registration section, check height after a delay
            if (e.target.closest('.registration-page')) {
                setTimeout(() => {
                    checkIframeHeight();
                    detectFormInteraction();
                }, 800);
            }
        });
        
        // Monitor scroll events that might indicate form expansion
        window.addEventListener('scroll', () => {
            if (document.querySelector('.registration-page')) {
                setTimeout(detectFormInteraction, 300);
            }
        });
        
        // Initial checks with staggered delays
        setTimeout(checkIframeHeight, 2000);
        setTimeout(detectFormInteraction, 3000);
        setTimeout(() => {
            // Final check to ensure proper state
            const iframeRect = iframe.getBoundingClientRect();
            if (iframeRect.height > window.innerHeight * 0.7) {
                expandRegistrationSection();
                iframe.classList.add('expanded');
            }
        }, 5000);
    }
    
    // Cleanup function
    function cleanup() {
        if (resizeObserver) {
            resizeObserver.disconnect();
            resizeObserver = null;
        }
        if (mutationObserver) {
            mutationObserver.disconnect();
            mutationObserver = null;
        }
        if (heightCheckInterval) {
            clearInterval(heightCheckInterval);
            heightCheckInterval = null;
        }
    }
    
    // Start monitoring
    findAndMonitorIframe();
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', cleanup);
}

// Helper function to get current page name
function getCurrentPageName() {
    const path = window.location.pathname;
    const page = path.split('/').pop();
    
    switch (page) {
        case 'index.html':
        case '':
            return 'Home';
        case 'the-academy.html':
            return 'The Academy';
        case 'junior-grassroots.html':
            return 'Junior Grassroots';
        case 'about.html':
            return 'About';
        case 'recruitment.html':
            return 'Recruitment';
        case 'wanaka-tournament.html':
            return 'Wanaka Tournament';
        case 'register.html':
            return 'Registration';
        default:
            return 'Website';
    }
}

// Dynamic fixture updates
function updateFixtures() {
    const fixtures = {
        lastMatch: {
            home: 'Wānaka FC',
            away: 'Nelson Suburbs',
            homeScore: 3,
            awayScore: 1,
            date: '2025-09-13'
        },
        nextMatch: {
            home: 'Wānaka FC',
            away: 'Valley Rovers',
            date: '2024-01-22',
            time: '14:00',
            location: 'Wanaka Sports Ground'
        }
    };
    
    const lastMatchCard = document.querySelector('.fixture-card:first-child');
    const nextMatchCard = document.querySelector('.fixture-card:last-child');
    
    if (lastMatchCard) {
        lastMatchCard.querySelector('.match-team').textContent = 
            `${fixtures.lastMatch.home} vs ${fixtures.lastMatch.away}`;
        lastMatchCard.querySelector('.match-result').textContent = 
            `${fixtures.lastMatch.homeScore} - ${fixtures.lastMatch.awayScore}`;
    }
    
    if (nextMatchCard) {
        nextMatchCard.querySelector('.match-team').textContent = 
            `${fixtures.nextMatch.home} vs ${fixtures.nextMatch.away}`;
        nextMatchCard.querySelector('.match-info').innerHTML = 
            `${fixtures.nextMatch.date} at ${fixtures.nextMatch.time}<br>${fixtures.nextMatch.location}`;
    }
}

document.addEventListener('DOMContentLoaded', updateFixtures);

// Landing Phase Transition functionality
function initLandingTransition() {
    const phase1 = document.querySelector('.phase-1');
    const phase2 = document.querySelector('.phase-2');
    
    if (!phase1 || !phase2) return;
    
    // Start the transition after 5 seconds
    setTimeout(() => {
        phase1.classList.remove('active');
        phase2.classList.add('active');
        
        // Trigger phase-2 text animations when the phase becomes active
        // The CSS animations will now start because .phase-2.active selectors are triggered
    }, 2500);
}


// Tournament Images Fullscreen Functionality
function initTournamentImageModal() {
    // Only run on tournament page
    if (!document.querySelector('.tournament-images-section')) return;
    
    const tournamentImages = document.querySelectorAll('.tournament-image img');
    let imageModal = null;
    
    // Create modal HTML structure
    function createModal() {
        const modal = document.createElement('div');
        modal.className = 'image-modal';
        modal.innerHTML = `
            <div class="modal-image-container">
                <span class="close-image-modal">&times;</span>
                <img class="modal-image" src="" alt="">
            </div>
        `;
        document.body.appendChild(modal);
        return modal;
    }
    
    // Open modal with image
    function openModal(imageSrc, imageAlt) {
        if (!imageModal) {
            imageModal = createModal();
        }
        
        const modalImage = imageModal.querySelector('.modal-image');
        modalImage.src = imageSrc;
        modalImage.alt = imageAlt;
        
        imageModal.classList.add('show');
        document.body.classList.add('modal-open');
        
        // Add event listeners for closing
        const closeBtn = imageModal.querySelector('.close-image-modal');
        closeBtn.addEventListener('click', closeModal);
        
        // Close on background click
        imageModal.addEventListener('click', (e) => {
            if (e.target === imageModal) {
                closeModal();
            }
        });
        
        // Close on ESC key
        document.addEventListener('keydown', handleEscKey);
    }
    
    // Close modal
    function closeModal() {
        if (imageModal) {
            imageModal.classList.remove('show');
            document.body.classList.remove('modal-open');
            
            // Remove ESC key listener
            document.removeEventListener('keydown', handleEscKey);
            
            // Clean up modal after animation
            setTimeout(() => {
                if (imageModal && imageModal.parentNode) {
                    imageModal.parentNode.removeChild(imageModal);
                    imageModal = null;
                }
            }, 300);
        }
    }
    
    // Handle ESC key press
    function handleEscKey(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    }
    
    // Add click event listeners to tournament images
    tournamentImages.forEach(img => {
        img.parentElement.addEventListener('click', (e) => {
            e.preventDefault();
            openModal(img.src, img.alt);
        });
        
        // Add keyboard accessibility
        img.parentElement.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openModal(img.src, img.alt);
            }
        });
        
        // Make focusable for keyboard navigation
        img.parentElement.setAttribute('tabindex', '0');
        img.parentElement.setAttribute('role', 'button');
        img.parentElement.setAttribute('aria-label', 'View image in fullscreen');
    });
}
