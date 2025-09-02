<?php
/**
 * Template Name: Contact Us
 * Description: صفحة تواصل معنا مع خريطة وفورم
 */

defined('ABSPATH') || exit;
get_header();
?>

<style>
        .contact-section {
            padding: 60px 0;
            background: #f8f9fa;
        }
        
        .map-container {
            height: 400px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 50px;
        }
        
        .contact-form {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .contact-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 15px;
            height: 100%;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-primary {
           background: linear-gradient(135deg, #0f3bff 0%, #4369fd 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .contact-info-item {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .contact-info-item i {
            font-size: 24px;
            margin-left: 15px;
            width: 40px;
        }
        
        .section-title {
            color: #333;
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        .section-subtitle {
            color: #666;
            margin-bottom: 40px;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #c3e6cb;
            margin-bottom: 20px;
            display: none;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #f5c6cb;
            margin-bottom: 20px;
            display: none;
        }
        
        @media (max-width: 768px) {
            .contact-form, .contact-info {
                padding: 30px 20px;
            }
            
            .map-container {
                height: 300px;
                margin-bottom: 30px;
            }
        }
    </style>
<section class="contact-section">
    <div class="container">
        
        <!-- Page Header -->
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h1 class="section-title display-4">Contact Us</h1>
                <p class="section-subtitle lead">We are here to help you answer all your questions</p>
            </div>
        </div>

        <!-- Google Map -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="map-container">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d55251.377009943226!2d31.223079284179688!3d30.059489000000003!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14583fa60b21beeb%3A0x79dfb296e8423bba!2sCairo%2C%20Cairo%20Governorate%2C%20Egypt!5e0!3m2!1sen!2seg!4v1703123456789!5m2!1sen!2seg" 
                        width="100%" 
                        height="100%" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </div>

        <!-- Contact Form & Info -->
        <div class="row">
            
            <!-- Contact Form -->
            <div class="col-lg-12 mb-4">
                <div class="contact-form">
                    <h3 class="mb-4">Send Us a Message</h3>
                    
                    <div class="success-message" id="success-message">
                        <i class="bi bi-check-circle"></i> Message sent successfully! We will get back to you soon.
                    </div>
                    
                    <div class="error-message" id="error-message">
                        <i class="bi bi-exclamation-circle"></i> An error occurred while sending the message. Please try again.
                    </div>
                    
                    <form id="contact-form" method="post" action="">
                        <?php wp_nonce_field('contact_form_nonce', 'contact_nonce'); ?>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Name *</label>
                                <input type="text" class="form-control" id="name" name="contact_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone *</label>
                                <input type="tel" class="form-control" id="phone" name="contact_phone" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="contact_email" required>
                        </div>

                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject *</label>
                            <input type="text" class="form-control" id="subject" name="contact_subject" required>
                        </div>

                        <div class="mb-4">
                            <label for="message" class="form-label">Message *</label>
                            <textarea class="form-control" id="message" name="contact_message" rows="6"
                                required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-send"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>

           
        </div>
    </div>
</section>

<!-- Bootstrap JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('contact-form');
        const successMsg = document.getElementById('success-message');
        const errorMsg = document.getElementById('error-message');

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            // Hide previous messages
            successMsg.style.display = 'none';
            errorMsg.style.display = 'none';

            // Get form data
            const formData = new FormData(form);
            formData.append('action', 'handle_contact_form');

            // Show loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> جاري الإرسال...';
            submitBtn.disabled = true;

            // Send AJAX request
            fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        successMsg.style.display = 'block';
                        form.reset();
                        // Scroll to success message
                        successMsg.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    } else {
                        errorMsg.style.display = 'block';
                        errorMsg.innerHTML = '<i class="bi bi-exclamation-circle"></i> ' +
                            (data.data && data.data.message ? data.data.message : 'حدث خطأ في إرسال الرسالة');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    errorMsg.style.display = 'block';
                })
                .finally(() => {
                    // Reset button
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
        });
    });
</script>

<?php
get_footer();
?>