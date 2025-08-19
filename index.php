<?php
session_start();
include 'includes/header.php';
include_once 'includes/config.php';
?>
<!-- Page: Home / Index -->

<!-- Hero -->
<section class="relative isolate">
  <!-- background image -->
  <img
    src="assets/images/bg.jpeg"
    alt="Freshly laundered clothes on a rack"
    class="absolute inset-0 h-full w-full object-cover"
  />
  <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-black/40 to-black/60"></div>

  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 md:py-32 text-center text-white">
    <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight">
      Spotless Solutions
    </h1>
    <p class="mt-4 text-lg md:text-xl text-white/90 max-w-3xl mx-auto">
      Professional laundry &amp; dry cleaning at your doorstep. Book online, relax, and wear fresh—every time.
    </p>
    <div class="mt-8 flex items-center justify-center gap-3">
      <?php if (!isset($_SESSION['user_id'])): ?>
        <a href="register.php" class="inline-flex items-center justify-center rounded-xl bg-white text-gray-900 px-6 py-3 font-semibold shadow hover:shadow-md transition">
          Get Started
        </a>
        <a href="login.php" class="inline-flex items-center justify-center rounded-xl bg-white/10 text-white px-6 py-3 font-semibold ring-1 ring-white/30 hover:bg-white/15 transition">
          Log In
        </a>
      <?php else: ?>
        <a href="booking.php" class="inline-flex items-center justify-center rounded-xl bg-white text-gray-900 px-6 py-3 font-semibold shadow hover:shadow-md transition">
          Book Now
        </a>
        <a href="logout.php" class="inline-flex items-center justify-center rounded-xl bg-white/10 text-white px-6 py-3 font-semibold ring-1 ring-white/30 hover:bg-white/15 transition">
          Log Out
        </a>
      <?php endif; ?>
    </div>

    <!-- trust badges -->
    <div class="mt-10 flex flex-wrap items-center justify-center gap-4 text-sm text-white/80">
      <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 ring-1 ring-white/20">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 13 4 4L19 7"/></svg>
        48h Turnaround
      </span>
      <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 ring-1 ring-white/20">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a7 7 0 0 0-7 7v2.278c0 .51-.195 1.002-.548 1.374L3 14.5V16h18v-1.5l-1.452-1.848a1.94 1.94 0 0 1-.548-1.374V9a7 7 0 0 0-7-7Z"/><path d="M7 16v1a5 5 0 0 0 10 0v-1H7Z"/></svg>
        Gentle on Fabrics
      </span>
      <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 ring-1 ring-white/20">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M4.5 6.75h15a.75.75 0 0 1 .75.75v8.25A2.25 2.25 0 0 1 18 18H6a2.25 2.25 0 0 1-2.25-2.25V7.5a.75.75 0 0 1 .75-.75Z"/><path d="M8.25 9.75h7.5v4.5h-7.5v-4.5Z"/></svg>
        Easy Online Booking
      </span>
    </div>
  </div>
</section>

<!-- Services -->
<section class="py-16 bg-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center">
      <h2 class="text-3xl md:text-4xl font-bold text-gray-900">Services that fit your life</h2>
      <p class="mt-3 text-gray-600 max-w-2xl mx-auto">From everyday wash &amp; fold to delicate dry cleaning and crisp ironing, we’ve got you covered.</p>
    </div>

    <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <!-- Card 1 -->
      <article class="group overflow-hidden rounded-2xl border bg-white shadow-sm hover:shadow-md transition">
        <div class="aspect-[4/3] overflow-hidden">
          <img src="assets/images/dry_cleaner.jpeg"
               alt="Dry cleaning of formal clothes"
               class="h-full w-full object-cover group-hover:scale-105 transition"/>
        </div>
        <div class="p-5">
          <h3 class="text-lg font-semibold text-gray-900">Dry Cleaning</h3>
          <p class="mt-1 text-sm text-gray-600">Perfect for suits, dresses, and delicate fabrics requiring a soft touch.</p>
        </div>
      </article>
      <!-- Card 2 -->
      <article class="group overflow-hidden rounded-2xl border bg-white shadow-sm hover:shadow-md transition">
        <div class="aspect-[4/3] overflow-hidden">
          <img src="assets/images/wash_fold.jpg"
               alt="Folded laundry neatly stacked"
               class="h-full w-full object-cover group-hover:scale-105 transition"/>
        </div>
        <div class="p-5">
          <h3 class="text-lg font-semibold text-gray-900">Wash &amp; Fold</h3>
          <p class="mt-1 text-sm text-gray-600">Everyday laundry picked up and delivered—sorted, washed, and neatly folded.</p>
        </div>
      </article>
      <!-- Card 3 -->
      <article class="group overflow-hidden rounded-2xl border bg-white shadow-sm hover:shadow-md transition">
        <div class="aspect-[4/3] overflow-hidden">
          <img src="assets/images/ironing_service.jpg"
               alt="Steam ironing on an ironing board"
               class="h-full w-full object-cover group-hover:scale-105 transition"/>
        </div>
        <div class="p-5">
          <h3 class="text-lg font-semibold text-gray-900">Ironing Services</h3>
          <p class="mt-1 text-sm text-gray-600">Crisp, wrinkle‑free clothes—ready to wear for work and events.</p>
        </div>
      </article>
      <!-- Card 4 -->
      <article class="group overflow-hidden rounded-2xl border bg-white shadow-sm hover:shadow-md transition">
        <div class="aspect-[4/3] overflow-hidden">
          <img src="assets/images/home_pickup.jpg"
               alt="Laundry pickup at home"
               class="h-full w-full object-cover group-hover:scale-105 transition"/>
        </div>
        <div class="p-5">
          <h3 class="text-lg font-semibold text-gray-900">Home Pickup</h3>
          <p class="mt-1 text-sm text-gray-600">Schedule a pickup—our team arrives at your doorstep on time.</p>
        </div>
      </article>
      <!-- Card 5 -->
      <article class="group overflow-hidden rounded-2xl border bg-white shadow-sm hover:shadow-md transition">
        <div class="aspect-[4/3] overflow-hidden">
          <img src="assets/images/product5.jpeg"
               alt="Eco friendly detergents and care"
               class="h-full w-full object-cover group-hover:scale-105 transition"/>
        </div>
        <div class="p-5">
          <h3 class="text-lg font-semibold text-gray-900">Eco Care</h3>
          <p class="mt-1 text-sm text-gray-600">Dermatologically safe detergents and fabric‑friendly cycles.</p>
        </div>
      </article>
      <!-- Card 6 -->
      <article class="group overflow-hidden rounded-2xl border bg-white shadow-sm hover:shadow-md transition">
        <div class="aspect-[4/3] overflow-hidden">
          <img src="assets/images/express_servoce.jpg"
               alt="Express laundry service"
               class="h-full w-full object-cover group-hover:scale-105 transition"/>
        </div>
        <div class="p-5">
          <h3 class="text-lg font-semibold text-gray-900">Express Service</h3>
          <p class="mt-1 text-sm text-gray-600">When time matters, get priority cleaning and fast delivery.</p>
        </div>
      </article>
    </div>
  </div>
</section>

<!-- How it works -->
<section class="py-16 bg-gradient-to-b from-blue-50 to-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center">
      <h2 class="text-3xl md:text-4xl font-bold text-gray-900">How it works</h2>
      <p class="mt-3 text-gray-600">Simple steps to spotless.</p>
    </div>

    <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
      <!-- step -->
      <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
        <div class="h-10 w-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold">1</div>
        <h3 class="mt-4 font-semibold text-gray-900">Book online</h3>
        <p class="mt-1 text-sm text-gray-600">Choose pickup/drop‑off or walk‑in and select your slot.</p>
      </div>
      <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
        <div class="h-10 w-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold">2</div>
        <h3 class="mt-4 font-semibold text-gray-900">We collect</h3>
        <p class="mt-1 text-sm text-gray-600">Our team arrives at your address—on time, every time.</p>
      </div>
      <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
        <div class="h-10 w-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold">3</div>
        <h3 class="mt-4 font-semibold text-gray-900">We clean</h3>
        <p class="mt-1 text-sm text-gray-600">Eco‑friendly detergents and gentle cycles for every fabric.</p>
      </div>
      <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
        <div class="h-10 w-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold">4</div>
        <h3 class="mt-4 font-semibold text-gray-900">We deliver</h3>
        <p class="mt-1 text-sm text-gray-600">Folded, fresh, and on schedule—ready to wear.</p>
      </div>
    </div>
  </div>
</section>

<!-- Why choose us -->
<section class="py-16 bg-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-2 gap-10 items-center">
    <div class="order-2 lg:order-1">
      <h2 class="text-3xl md:text-4xl font-bold text-gray-900">Why customers trust Spotless</h2>
      <p class="mt-3 text-gray-600">Built for convenience, reliability, and garment care you can feel.</p>
      <ul class="mt-6 space-y-3 text-gray-700">
        <li class="flex gap-3"><span class="mt-1 h-5 w-5 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs">✓</span> Real‑time booking &amp; updates</li>
        <li class="flex gap-3"><span class="mt-1 h-5 w-5 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs">✓</span> Experienced fabric specialists</li>
        <li class="flex gap-3"><span class="mt-1 h-5 w-5 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs">✓</span> On‑time pickup &amp; delivery</li>
        <li class="flex gap-3"><span class="mt-1 h-5 w-5 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs">✓</span> Transparent pricing</li>
      </ul>
      <div class="mt-8">
        <?php if (!isset($_SESSION['user_id'])): ?>
          <a href="register.php" class="inline-flex items-center rounded-xl bg-blue-600 text-white px-6 py-3 font-semibold shadow hover:bg-blue-700">Create an account</a>
        <?php else: ?>
          <a href="booking.php" class="inline-flex items-center rounded-xl bg-blue-600 text-white px-6 py-3 font-semibold shadow hover:bg-blue-700">Book your slot</a>
        <?php endif; ?>
      </div>
    </div>
    <div class="order-1 lg:order-2">
      <div class="relative">
        <img
          src="assets/images/laundry_machine.jpg"
          alt="Laundry shop interior with machines"
          class="rounded-2xl shadow-lg object-cover w-full"
        />
        <div class="absolute -bottom-6 -left-6 hidden sm:block">
          <img
            src="assets/images/fold_cleaned.jpg"
            alt="Basket with neatly folded clothes"
            class="w-40 h-40 object-cover rounded-xl ring-4 ring-white shadow-md"
          />
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Testimonials -->
<section class="py-16 bg-gradient-to-b from-white to-blue-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center">
      <h2 class="text-3xl md:text-4xl font-bold text-gray-900">Loved by busy people</h2>
      <p class="mt-3 text-gray-600">Here’s what our customers say.</p>
    </div>

    <div class="mt-10 grid gap-6 md:grid-cols-3">
      <figure class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
        <blockquote class="text-gray-700">“Booked pickup in 2 minutes, got everything back fresh and folded. Life saver!”</blockquote>
        <figcaption class="mt-4 text-sm text-gray-500">— Priya S.</figcaption>
      </figure>
      <figure class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
        <blockquote class="text-gray-700">“Delicates came back perfect. The team really knows fabrics.”</blockquote>
        <figcaption class="mt-4 text-sm text-gray-500">— Arjun M.</figcaption>
      </figure>
      <figure class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
        <blockquote class="text-gray-700">“Super convenient. On‑time pickup and delivery every time.”</blockquote>
        <figcaption class="mt-4 text-sm text-gray-500">— Nisha K.</figcaption>
      </figure>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="py-16 bg-white">
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="rounded-3xl bg-blue-600 text-white p-8 md:p-10 shadow-lg">
      <div class="md:flex md:items-center md:justify-between gap-6">
        <div>
          <h3 class="text-2xl md:text-3xl font-bold">Ready for consistently fresh clothes?</h3>
          <p class="mt-2 text-white/90">Schedule your first pickup today—your wardrobe will thank you.</p>
        </div>
        <div class="mt-6 md:mt-0">
          <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="register.php" class="inline-flex items-center rounded-xl bg-white text-blue-700 px-6 py-3 font-semibold shadow hover:bg-blue-50">Get started</a>
          <?php else: ?>
            <a href="booking.php" class="inline-flex items-center rounded-xl bg-white text-blue-700 px-6 py-3 font-semibold shadow hover:bg-blue-50">Book now</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
