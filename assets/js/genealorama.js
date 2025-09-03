/**
 * Genealorama Embed JavaScript
 * Handles iframe resizing and communication
 */

document.addEventListener("DOMContentLoaded", () => {
    const iframes = document.querySelectorAll('[id^="wpGenearamaIframe_"]');
    
    iframes.forEach(iframe => {
        if (iframe) {
            // Initial height based on window
            function setInitialHeight() {
                const windowHeight = window.innerHeight;
                const offsetTop = iframe.getBoundingClientRect().top;
                const newHeight = windowHeight - offsetTop - 40;
                iframe.style.height = Math.max(700, newHeight) + "px";
            }
            
            setInitialHeight();
            
            // Listen for iframe messages
            window.addEventListener("message", (event) => {
                // Security: check origin
                if (!event.origin.includes("familystory.live") && !event.origin.includes("genealogie.app") && !event.origin.includes("genealorama.com")) return;
                
                // Handle automatic height adjustment
                if (event.data.genealoramaHeight && !isNaN(event.data.genealoramaHeight)) {
                    iframe.style.height = event.data.genealoramaHeight + "px";
                }
                
                // Handle authentication errors
                if (event.data.error === 'invalid_signature' || event.data.error === 'authentication_failed') {
                    console.error('Genealorama authentication error:', event.data.error);
                    
                    if (genealoramaEmbed.isAdmin) {
                        // For admins, show message with link to settings
                        const container = iframe.parentElement;
                        const warningDiv = container.querySelector('.genealorama-auth-warning');
                        
                        if (!warningDiv) {
                            const warning = document.createElement('div');
                            warning.className = 'genealorama-auth-warning';
                            warning.style.cssText = 'background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; margin-bottom: 10px; border-radius: 4px;';
                            warning.innerHTML = '<strong>Authentication Error:</strong> Genealorama credentials are invalid. ' +
                                              '<a href="' + genealoramaEmbed.settingsUrl + '">Update settings</a>.';
                            container.insertBefore(warning, iframe);
                        }
                    } else {
                        // For non-admin users, show generic message
                        const container = iframe.parentElement;
                        container.innerHTML = '<p style="padding: 20px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px;">' +
                                            'An error occurred while loading. Please contact the site administrator.</p>';
                    }
                }
                
                // Handle home button - return to WordPress homepage
                if (event.data.action === 'returnToHome' && event.data.source === 'genealorama') {
                    console.log('Navigation: return to home requested by Genealorama');
                    window.location.href = genealoramaEmbed.homeUrl;
                }
            });
            
            window.addEventListener("resize", setInitialHeight);
        }
    });
});