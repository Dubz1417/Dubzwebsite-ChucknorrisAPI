<?php 
require_once 'auth_check.php';
$page_title = "Tours - Philippines Travel & Tours";
require_once 'db_config.php';
include 'header.php'; 

$db = getDBConnection();
$stmt = $db->query("SELECT * FROM tours ORDER BY id");
$tours = $stmt->fetchAll();
?>

<main>
    <section class="page-header">
        <div class="container">
            <h1>Mountain Tour Packages</h1>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <?php if (count($tours) > 0): ?>
                <div class="tours-grid">
                    <?php foreach ($tours as $tour): ?>
                        <div class="tour-card">
                            <div class="tour-image">
                                <img src="<?php echo htmlspecialchars($tour['image_url']); ?>" alt="<?php echo htmlspecialchars($tour['title']); ?>">
                                <div class="tour-badge">₱<?php echo number_format($tour['price'], 2); ?></div>
                            </div>
                            <div class="tour-content">
                                <h3 class="tour-title"><?php echo htmlspecialchars($tour['title']); ?></h3>
                                <p class="tour-location">📍 <?php echo htmlspecialchars($tour['location']); ?></p>
                                <p class="tour-description"><?php echo htmlspecialchars($tour['description']); ?></p>
                                <div class="tour-meta">
                                    <span class="tour-duration">⏱️ <?php echo htmlspecialchars($tour['duration']); ?></span>
                                </div>
                                <button class="btn btn-primary btn-full book-now-btn" 
                                        data-tour-id="<?php echo $tour['id']; ?>"
                                        data-tour-name="<?php echo htmlspecialchars($tour['title']); ?>"
                                        data-tour-price="<?php echo $tour['price']; ?>"
                                        data-tour-location="<?php echo htmlspecialchars($tour['location']); ?>">
                                    Book Now
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-tours">  
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>


<div id="bookingModal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2 id="modalTourName">Book Your Tour</h2>
        <p class="modal-subtitle" id="modalTourLocation"></p>
        
        <form id="bookingForm">
            <input type="hidden" id="tourId" name="tour_id">
            <input type="hidden" id="tourName" name="tour_name">
            <input type="hidden" id="tourPrice" name="tour_price">
            
            <div class="form-group">
                <label for="customerName">Full Name </label>
                <input type="text" id="customerName" name="customer_name" required placeholder="">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="customerEmail">Email Address </label>
                    <input type="email" id="customerEmail" name="customer_email" required placeholder="">
                </div>
                
                <div class="form-group">
                    <label for="customerPhone">Phone Number</label>
                    <input type="tel" id="customerPhone" name="customer_phone" placeholder="">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="numberOfPeople">Number of People </label>
                    <input type="number" id="numberOfPeople" name="number_of_people" min="1" value="" required>
                </div>
                
                <div class="form-group">
                    <label for="travelDate">Travel Date </label>
                    <input type="date" id="travelDate" name="travel_date" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="specialRequests">Special Requests</label>
                <textarea id="specialRequests" name="special_requests" rows="3" placeholder=""></textarea>
            </div>
            
            <div class="booking-total">
                <span>Total Price:</span>
                <span id="totalPrice" class="total-amount">₱0.00</span>
            </div>
            
            <button type="submit" class="btn btn-primary btn-full">Confirm Booking</button>
        </form>
        
        <div id="bookingMessage" class="form-message" style="display: none;"></div>
    </div>
</div>

<script>

const modal = document.getElementById('bookingModal');
const closeBtn = document.querySelector('.close-modal');
const bookingForm = document.getElementById('bookingForm');
const bookNowBtns = document.querySelectorAll('.book-now-btn');


const travelDateInput = document.getElementById('travelDate');
const today = new Date().toISOString().split('T')[0];
travelDateInput.setAttribute('min', today);


bookNowBtns.forEach(btn => {
    btn.addEventListener('click', function() {
        const tourId = this.getAttribute('data-tour-id');
        const tourName = this.getAttribute('data-tour-name');
        const tourPrice = parseFloat(this.getAttribute('data-tour-price'));
        const tourLocation = this.getAttribute('data-tour-location');
        
        document.getElementById('modalTourName').textContent = tourName;
        document.getElementById('modalTourLocation').textContent = '📍 ' + tourLocation;
        document.getElementById('tourId').value = tourId;
        document.getElementById('tourName').value = tourName;
        document.getElementById('tourPrice').value = tourPrice;
        
        updateTotalPrice();
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    });
});


closeBtn.addEventListener('click', function() {
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
    bookingForm.reset();
    document.getElementById('bookingMessage').style.display = 'none';
});


window.addEventListener('click', function(event) {
    if (event.target === modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        bookingForm.reset();
        document.getElementById('bookingMessage').style.display = 'none';
    }
});


function updateTotalPrice() {
    const price = parseFloat(document.getElementById('tourPrice').value) || 0;
    const people = parseInt(document.getElementById('numberOfPeople').value) || 1;
    const total = price * people;
    document.getElementById('totalPrice').textContent = '₱' + total.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

document.getElementById('numberOfPeople').addEventListener('input', updateTotalPrice);


bookingForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const bookingMessage = document.getElementById('bookingMessage');
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Processing...';
    
    try {
        const formData = new FormData(this);
        const response = await fetch('submit_booking.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        bookingMessage.style.display = 'block';
        
        if (data.success) {
            bookingMessage.className = 'form-message success';
            bookingMessage.innerHTML = `<p>✓ ${data.message}</p>`;
            this.reset();
            
            setTimeout(() => {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
                bookingMessage.style.display = 'none';
            }, 4000);
        } else {
            bookingMessage.className = 'form-message error';
            bookingMessage.innerHTML = `<p>✗ ${data.message}</p>`;
        }
        
    } catch (error) {
        bookingMessage.style.display = 'block';
        bookingMessage.className = 'form-message error';
        bookingMessage.innerHTML = '<p>✗ An error occurred. Please try again.</p>';
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalBtnText;
    }
});
</script>

<?php include 'footer.php'; ?>
