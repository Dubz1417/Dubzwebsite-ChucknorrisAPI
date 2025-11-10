<?php 
require_once 'auth_check.php';
$page_title = "Contact Us - Dubz Adventours";
include 'header.php'; 
?>

<main>
    <section class="page-header">
        <div class="container">
            <p>Ready to start your Philippine Mountain Expedition? We're here to help!</p>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="contact-wrapper">
                <div class="contact-info">
                    <h2>Contact Information</h2>
                    <p class="contact-intro"></p>
                    
                    <div class="contact-details">
                        <div class="contact-item">
                            <div class="contact-icon">📍</div>
                            <div class="contact-text">
                                <h3>We are located</h3>
                                <p>Purok Pluto<br>Balutakay Managa Bansalan<br>Davao Del Sur Philippines</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">📧</div>
                            <div class="contact-text">
                                <h3>Email Us</h3>
                                <p><a href="dubriajoemar@smcbi.edu.ph">dubriajoemar@smcbi.edu.ph</a></p>
                                <p><a href="mailto:adubria11@gmail.com">adubria11@gmail.com</a></p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">📱</div>
                            <div class="contact-text">
                                <h3>Call Us</h3>
                                <p>Mobile: +63 90838131514</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">🕒</div>
                            <div class="contact-text">
                                <h3>Business Hours</h3>
                                <p>Monday - Friday: 9:00 AM - 6:00 PM</p>
                                <p>Saturday: 10:00 AM - 4:00 PM</p>
                                <p>Sunday: Closed</p>
                            </div>
                        </div>
                    </div>

                    <div class="social-media">
                        <h3>Follow Us</h3>
                        <div class="social-links">
                            <a href="https://facebook.com/dubzadventours" target="_blank" class="social-link facebook">
                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                Facebook
                            </a>    
                        </div>
                    </div>
                </div>

                <div class="contact-form-wrapper">
                    <h2>Send Us a Message</h2>
                    <form class="contact-form" id="contactForm">
                        <div class="form-group">
                            <label for="name">Full Name *</label>
                            <input type="text" id="name" name="name" required placeholder="">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email Address *</label>
                                <input type="email" id="email" name="email" required placeholder="">
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" placeholder="+63">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Subject *</label>
                            <select id="subject" name="subject" required>
                                <option value="">Select a subject...</option>
                                <option value="tour-inquiry">Tour Inquiry</option>
                                <option value="booking">Booking Request</option>
                                <option value="custom-tour">Custom Tour Package</option>
                                <option value="feedback">Feedback</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message *</label>
                            <textarea id="message" name="message" rows="6" required placeholder="Tell us about your dream Mountain..."></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-full">Send Message</button>
                    </form>
                    
                    <div id="formMessage" class="form-message" style="display: none;"></div>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
document.getElementById('contactForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formMessage = document.getElementById('formMessage');
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Sending...';
    
    try {
        const formData = new FormData(this);
        const response = await fetch('submit_contact.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        formMessage.style.display = 'block';
        
        if (data.success) {
            formMessage.className = 'form-message success';
            formMessage.innerHTML = `<p>✓ ${data.message}</p>`;
            this.reset();
        } else {
            formMessage.className = 'form-message error';
            formMessage.innerHTML = `<p>✗ ${data.message}</p>`;
        }
        
        setTimeout(() => {
            formMessage.style.display = 'none';
        }, 5000);
        
    } catch (error) {
        formMessage.style.display = 'block';
        formMessage.className = 'form-message error';
        formMessage.innerHTML = '<p>✗ An error occurred. Please try again.</p>';
        
        setTimeout(() => {
            formMessage.style.display = 'none';
        }, 5000);
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalBtnText;
    }
});
</script>

<?php include 'footer.php'; ?>
