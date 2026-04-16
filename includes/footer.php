<!-- ===== FOOTER ===== -->
<footer class="site-footer">
  <div class="footer-container">

    <!-- Brand column -->
    <div class="footer-col footer-brand">
      <h3><i class="fas fa-utensils"></i> RestauRant</h3>
      <p>Experience the finest flavours crafted with passion. Fresh ingredients, warm ambience, unforgettable dining.</p>
      <div class="social-icons">
        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
        <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
      </div>
    </div>

    <!-- Quick Links -->
    <div class="footer-col">
      <h4>Quick Links</h4>
      <ul>
        <li><a href="<?= BASE_URL ?>/index.php">Home</a></li>
        <li><a href="<?= BASE_URL ?>/menu.php">Our Menu</a></li>
        <li><a href="<?= BASE_URL ?>/booking.php">Book a Table</a></li>
        <li><a href="<?= BASE_URL ?>/order-history.php">My Orders</a></li>
        <li><a href="<?= BASE_URL ?>/wishlist.php">Wishlist</a></li>
      </ul>
    </div>

    <!-- Contact -->
    <div class="footer-col">
      <h4>Contact Us</h4>
      <ul class="contact-list">
        <li><i class="fas fa-map-marker-alt"></i> 42 Food Street, Dhaka, Bangladesh</li>
        <li><i class="fas fa-phone"></i> +880 1700-000000</li>
        <li><i class="fas fa-envelope"></i> hello@restaurant.com</li>
        <li><i class="fas fa-clock"></i> Mon–Sun: 10:00 AM – 11:00 PM</li>
      </ul>
    </div>

    <!-- Newsletter -->
    <div class="footer-col">
      <h4>Newsletter</h4>
      <p>Subscribe for exclusive deals and new menu updates.</p>
      <form class="footer-newsletter" onsubmit="return false;">
        <input type="email" placeholder="Your email address" aria-label="Email for newsletter">
        <button type="submit"><i class="fas fa-paper-plane"></i></button>
      </form>
    </div>

  </div>

  <div class="footer-bottom">
    <p>&copy; <?= date('Y') ?> RestauRant. All rights reserved. Made with <i class="fas fa-heart" style="color:#ff6b35"></i></p>
  </div>
</footer>

<!-- Back to Top -->
<button class="back-to-top" id="backToTop" aria-label="Back to top">
  <i class="fas fa-chevron-up"></i>
</button>

<!-- Toast Container -->
<div id="toastContainer" aria-live="polite"></div>

<!-- Cart Sidebar -->
<div class="cart-overlay" id="cartOverlay"></div>
<aside class="cart-sidebar" id="cartSidebar">
  <div class="cart-sidebar-header">
    <h3><i class="fas fa-shopping-cart"></i> Your Cart</h3>
    <button id="cartClose" aria-label="Close cart"><i class="fas fa-times"></i></button>
  </div>
  <div class="cart-sidebar-items" id="cartSidebarItems">
    <p class="cart-empty-msg">Your cart is empty.</p>
  </div>
  <div class="cart-sidebar-footer" id="cartSidebarFooter" style="display:none">
    <div class="cart-total-row">
      <span>Total:</span>
      <strong id="cartSidebarTotal">৳0.00</strong>
    </div>
    <a href="<?= BASE_URL ?>/checkout.php" class="btn-primary btn-block">Proceed to Checkout</a>
  </div>
</aside>

<!-- Scripts -->
<script src="<?= BASE_URL ?>/js/app.js"></script>
<script src="<?= BASE_URL ?>/js/cart.js"></script>
</body>
</html>
