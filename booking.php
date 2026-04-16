<?php
// ============================================================
//  booking.php
// ============================================================
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Book a Table';
include __DIR__ . '/includes/header.php';

$timeSlots = [
    '10:00 AM','11:00 AM','12:00 PM','01:00 PM','02:00 PM',
    '03:00 PM','04:00 PM','05:00 PM','06:00 PM','07:00 PM',
    '08:00 PM','09:00 PM','10:00 PM',
];
?>

<div class="page-header">
  <div class="container">
    <h1><i class="fas fa-calendar-check"></i> Book a Table</h1>
    <p>Reserve your spot – we'll take care of the rest.</p>
    <div class="breadcrumb"><a href="<?= BASE_URL ?>/index.php">Home</a> / Booking</div>
  </div>
</div>

<section class="booking-page">
  <div class="container">

    <?php if (!isLoggedIn()): ?>
    <!-- Not logged in prompt -->
    <div style="max-width:520px;margin:0 auto;text-align:center;padding:3rem 2rem">
      <div style="font-size:4rem;color:var(--accent);margin-bottom:1rem"><i class="fas fa-user-lock"></i></div>
      <h2 style="margin-bottom:.75rem">Login Required</h2>
      <p style="color:var(--text-secondary);margin-bottom:2rem">
        Please sign in or create an account to book a table.
      </p>
      <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap">
        <a href="<?= BASE_URL ?>/login.php?redirect=<?= urlencode('/booking.php') ?>" class="btn-primary">
          <i class="fas fa-sign-in-alt"></i> Sign In
        </a>
        <a href="<?= BASE_URL ?>/register.php" class="btn-outline">
          <i class="fas fa-user-plus"></i> Register
        </a>
      </div>
    </div>

    <?php else: ?>

    <div class="booking-grid">

      <!-- Booking Form -->
      <div>
        <div class="checkout-card">
          <h3><i class="fas fa-calendar-alt"></i> Select Date & Time</h3>

          <!-- Success message (hidden by default) -->
          <div id="bookingSuccess" style="display:none;background:#e8f5e9;border:1px solid #a5d6a7;border-radius:12px;padding:1.25rem;margin-bottom:1.5rem;color:#2e7d32;text-align:center">
            <i class="fas fa-check-circle" style="font-size:1.75rem;margin-bottom:.5rem;display:block"></i>
            <strong>Booking Confirmed!</strong><br>We'll contact you shortly to confirm your reservation.
          </div>

          <form id="bookingForm" novalidate>
            <!-- Date -->
            <div class="form-group">
              <label class="form-label" for="bookingDate">Date</label>
              <div class="input-group">
                <i class="input-icon fas fa-calendar"></i>
                <input type="date" id="bookingDate" class="form-control" style="padding-left:2.5rem">
              </div>
            </div>

            <!-- Time Slots -->
            <div class="form-group">
              <label class="form-label">Time Slot</label>
              <div class="time-slots-grid">
                <?php foreach ($timeSlots as $slot): ?>
                <button type="button" class="time-slot-btn"
                        data-slot="<?= htmlspecialchars($slot) ?>">
                  <?= htmlspecialchars($slot) ?>
                </button>
                <?php endforeach; ?>
              </div>
              <input type="hidden" id="selectedTimeSlot" value="">
            </div>

            <!-- Guest Count -->
            <div class="form-group">
              <label class="form-label">Number of Guests</label>
              <div class="guest-counter">
                <button type="button" id="guestMinus" class="btn-outline btn-sm" style="border-radius:50%;width:38px;height:38px;padding:0;justify-content:center">
                  <i class="fas fa-minus"></i>
                </button>
                <span class="guest-count-display" id="guestCountDisplay">2</span>
                <button type="button" id="guestPlus" class="btn-outline btn-sm" style="border-radius:50%;width:38px;height:38px;padding:0;justify-content:center">
                  <i class="fas fa-plus"></i>
                </button>
              </div>
              <input type="hidden" id="guestCountInput" name="guests" value="2">
            </div>

            <!-- Notes -->
            <div class="form-group">
              <label class="form-label" for="bookingNotes">Special Notes (optional)</label>
              <textarea id="bookingNotes" class="form-control" rows="3"
                        placeholder="Window seat, birthday celebration, dietary requirements…"></textarea>
            </div>

            <button type="submit" class="btn-primary btn-block btn-ripple">
              <i class="fas fa-calendar-check"></i> Confirm Booking
            </button>
          </form>
        </div>
      </div>

      <!-- Info / Policy -->
      <div>
        <div class="checkout-card">
          <h3><i class="fas fa-info-circle"></i> Booking Information</h3>
          <ul style="list-style:none;display:flex;flex-direction:column;gap:1rem">
            <?php
            $info = [
              ['fa-clock',         'Reservations',   'Available from 10:00 AM to 10:00 PM daily.'],
              ['fa-users',         'Party Size',      'We accommodate 1 to 20 guests per booking.'],
              ['fa-check-circle',  'Confirmation',    'You\'ll receive a confirmation once we approve your booking.'],
              ['fa-times-circle',  'Cancellation',    'Please cancel at least 2 hours before your reservation.'],
              ['fa-phone',         'Help',            'Call us on +880 1700-000000 for urgent bookings.'],
            ];
            foreach ($info as $i):
            ?>
            <li style="display:flex;gap:.85rem;align-items:flex-start">
              <i class="fas <?= $i[0] ?>" style="color:var(--accent);margin-top:.2rem;flex-shrink:0;width:16px"></i>
              <div>
                <div style="font-weight:600;font-size:.9rem"><?= $i[1] ?></div>
                <div style="font-size:.83rem;color:var(--text-muted);margin-top:.15rem"><?= $i[2] ?></div>
              </div>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <div style="margin-top:1.5rem;background:linear-gradient(135deg,#1a1a2e,#0f3460);border-radius:var(--border-radius);padding:2rem;text-align:center;color:#fff">
          <i class="fas fa-utensils" style="font-size:2.5rem;color:var(--accent);margin-bottom:1rem;display:block"></i>
          <h3 style="margin-bottom:.5rem">Private Events?</h3>
          <p style="color:rgba(255,255,255,.7);font-size:.9rem;margin-bottom:1.25rem">
            Planning a birthday, anniversary or corporate dinner? We offer exclusive private dining packages.
          </p>
          <a href="tel:+8801700000000" class="btn-primary btn-sm">
            <i class="fas fa-phone"></i> Call Us
          </a>
        </div>
      </div>

    </div>
    <?php endif; ?>

  </div>
</section>

<script>window.APP_BASE = '<?= BASE_URL ?>';</script>
<script src="<?= BASE_URL ?>/js/booking.js"></script>
<?php include __DIR__ . '/includes/footer.php'; ?>
