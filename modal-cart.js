/* =========================================================
   CART MODAL – INITIAL DATA, PRICE, SIZE, SUGAR, QUANTITY LOGIC
========================================================= */
document.addEventListener("DOMContentLoaded", function() {
  const cartModal = document.getElementById("cartModal");
  const modalName = document.getElementById("modalName");
  const modalImage = document.getElementById("modalImage");
  const modalPrice = document.getElementById("modalPrice");
  const quantityInput = document.getElementById("quantity");
  const decreaseBtn = document.getElementById("decrease");
  const increaseBtn = document.getElementById("increase");

  // Get reference to hidden form fields
  const formName = document.getElementById("formName");
  const formCategory = document.getElementById("formCategory");
  const formPrice = document.getElementById("formPrice");
  const formQty = document.getElementById("formQty");
  const formSize = document.getElementById("formSize");
  const formSugar = document.getElementById("formSugar");
  const formDiscount = document.getElementById("formDiscount");
  const formOrderType = document.getElementById("formOrderType");
  const formNickname = document.getElementById("formNickname");

  let summary = document.createElement("p");
  summary.id = "orderSummary";
  summary.className = "text-muted small mb-3";
  modalPrice.insertAdjacentElement("afterend", summary);

  let basePrice = 0; // UNIT price (price per item)
  let prices = {};

  // Prevent double submission flag
  let isAddingToCart = false;

  /* =========================================================
     NICKNAME SYNCHRONIZATION
  ========================================================= */
  const nicknameInput = document.getElementById('customerNickname');
  if (nicknameInput) {
    nicknameInput.addEventListener('input', function() {
      formNickname.value = this.value || "Guest";
    });
    
    // Initialize on page load
    formNickname.value = nicknameInput.value || "Guest";
  }

  /* =========================================================
     MODAL SHOW EVENT - Setup product data
  ========================================================= */
  cartModal.addEventListener("show.bs.modal", function (event) {
    const button = event.relatedTarget;

    // Store all prices for different sizes - only store what exists
    const dataSmall = button.getAttribute("data-small");
    const dataMedium = button.getAttribute("data-medium");
    const dataLarge = button.getAttribute("data-large");

    // Clear previous prices
    prices = {};

    // Check which sizes actually exist in the modal HTML
    if (document.getElementById("sizeSmall") && dataSmall) {
      prices.small = parseFloat(dataSmall.replace(/[^\d.]/g, ""));
    }
    
    if (document.getElementById("sizeMedium") && dataMedium) {
      prices.medium = parseFloat(dataMedium.replace(/[^\d.]/g, ""));
    }
    
    if (document.getElementById("sizeLarge") && dataLarge) {
      prices.large = parseFloat(dataLarge.replace(/[^\d.]/g, ""));
    }

    // NEW: read discount from product data
    const productDiscount = parseFloat(button.getAttribute("data-discount")) || 0;

    // Store size prices as data attributes on existing radio buttons
    if (document.getElementById("sizeSmall")) {
      document.getElementById("sizeSmall").setAttribute("data-price-small", prices.small || 0);
    }
    if (document.getElementById("sizeMedium")) {
      document.getElementById("sizeMedium").setAttribute("data-price-medium", prices.medium || 0);
    }
    if (document.getElementById("sizeLarge")) {
      document.getElementById("sizeLarge").setAttribute("data-price-large", prices.large || 0);
    }

    // Set basePrice to first available size
    let firstSize = Object.keys(prices)[0];
    basePrice = prices[firstSize] || 0;

    modalName.textContent = button.getAttribute("data-name");
    modalImage.src = button.getAttribute("data-image");
    modalPrice.textContent = "₱" + basePrice.toLocaleString();

    // Store product category in modal dataset
    cartModal.dataset.category = button.getAttribute("data-category");

    // Update hidden form fields
    formName.value = button.getAttribute("data-name");
    formCategory.value = button.getAttribute("data-category");
    formPrice.value = basePrice; // Set UNIT price (price per item)
    formQty.value = 1;
    formSize.value = firstSize ? firstSize.charAt(0).toUpperCase() + firstSize.slice(1) : "Small"; // Use first available size
    formSugar.value = "50%"; // Default sugar

    // NEW: set discount from product
    formDiscount.value = productDiscount;

    // Update nickname from input field
    if (nicknameInput) {
      formNickname.value = nicknameInput.value || "Guest";
    }

    // Update order type from radio
    const orderTypeRadio = document.querySelector('input[name="order_type"]:checked');
    if (orderTypeRadio) {
      formOrderType.value = orderTypeRadio.value;
    } else {
      formOrderType.value = "Dine In";
    }

    quantityInput.value = 1;
    
    // Check the first available size radio button
    const firstSizeRadio = document.querySelector('input[name="size"]:first-of-type');
    if (firstSizeRadio) {
      firstSizeRadio.checked = true;
    }
    
    document.getElementById("sugar50").checked = true;

    updateSummary();
    updateDisplayPrice();
  });

  /* =========================================================
     HELPER FUNCTIONS
  ========================================================= */
  function updateDisplayPrice() {
    const qty = parseInt(quantityInput.value) || 1;
    const discountPercent = parseFloat(formDiscount.value) || 0;

    // ✨ APPLY DISCOUNT % CORRECTLY
    let totalDisplayPrice = basePrice * qty;
    let discountAmount = totalDisplayPrice * (discountPercent / 100);
    let finalPrice = Math.max(totalDisplayPrice - discountAmount, 0);

    modalPrice.textContent = "₱" + finalPrice.toLocaleString();

    updateSummary();
  }

  function updateSummary() {
    const sizeElement = document.querySelector('input[name="size"]:checked + label');
    const sugarElement = document.querySelector('input[name="sugar"]:checked + label');
    const discount = parseFloat(formDiscount.value) || 0;

    const size = sizeElement ? sizeElement.textContent : "Small";
    const sugar = sugarElement ? sugarElement.textContent : "50%";
    
    summary.textContent = `${size} • ${sugar} Sugar • Discount: ${discount.toLocaleString()}%`;
  }

  function updateFormFields() {
    // Update size
    const sizeRadios = document.querySelectorAll('input[name="size"]');
    sizeRadios.forEach(radio => {
      if (radio.checked) {
        const sizeLabel = document.querySelector(`label[for="${radio.id}"]`);
        if (sizeLabel) {
          formSize.value = sizeLabel.textContent;
        }
      }
    });

    // Update sugar
    const sugarRadios = document.querySelectorAll('input[name="sugar"]');
    sugarRadios.forEach(radio => {
      if (radio.checked) {
        const sugarLabel = document.querySelector(`label[for="${radio.id}"]`);
        if (sugarLabel) {
          formSugar.value = sugarLabel.textContent;
        }
      }
    });

    // Update quantity
    const qty = parseInt(quantityInput.value) || 1;
    formQty.value = qty;

    // Update unit price based on selected size
    const selectedSizeRadio = document.querySelector('input[name="size"]:checked');
    if (selectedSizeRadio) {
      const sizeId = selectedSizeRadio.id.replace('size', '').toLowerCase();
      if (prices[sizeId]) {
        formPrice.value = prices[sizeId];
      }
    }

    // Update nickname
    if (nicknameInput) {
      formNickname.value = nicknameInput.value || "Guest";
    }

    // Update order type
    const orderTypeRadio = document.querySelector('input[name="order_type"]:checked');
    if (orderTypeRadio) {
      formOrderType.value = orderTypeRadio.value;
    } else {
      formOrderType.value = "Dine In";
    }
  }

  function updateQuantity() {
    let qty = parseInt(quantityInput.value) || 1;

    if (qty < 1 || isNaN(qty)) {
      qty = 1;
      quantityInput.value = 1;
    }

    formQty.value = qty;
    updateDisplayPrice();
  }

  /* =========================================================
     QUANTITY CONTROLS
  ========================================================= */
  decreaseBtn.addEventListener("click", () => {
    let qty = parseInt(quantityInput.value) || 1;
    quantityInput.value = Math.max(1, qty - 1);
    updateQuantity();
  });

  increaseBtn.addEventListener("click", () => {
    let qty = parseInt(quantityInput.value) || 1;
    quantityInput.value = qty + 1;
    updateQuantity();
  });

  quantityInput.addEventListener("input", () => {
    quantityInput.value = quantityInput.value.replace(/[^0-9]/g, '');
    updateQuantity();
  });

  quantityInput.addEventListener("blur", () => {
    if (!quantityInput.value || quantityInput.value === '0') {
      quantityInput.value = 1;
    }
    updateQuantity();
  });

  /* =========================================================
     SIZE AND SUGAR CONTROLS
  ========================================================= */
  document.querySelectorAll('input[name="size"]').forEach(radio => {
    radio.addEventListener("change", () => {
      const sizeId = radio.id.replace('size', '').toLowerCase();
      if (prices[sizeId]) {
        basePrice = prices[sizeId];
      }

      updateDisplayPrice();
      updateFormFields();
    });
  });

  document.querySelectorAll('input[name="sugar"]').forEach(radio => {
    radio.addEventListener("change", () => {
      updateSummary();
      updateFormFields();
    });
  });

  /* =========================================================
     ADD TO CART BUTTON - AJAX VERSION (NO PAGE REFRESH)
  ========================================================= */
  document.getElementById('addCartBtn').addEventListener('click', function(e) {
    e.preventDefault();
    e.stopPropagation();

    if (isAddingToCart) {
      return false;
    }

    isAddingToCart = true;

    // Update form fields before submitting
    updateFormFields();

    const addCartBtn = this;
    addCartBtn.disabled = true;
    addCartBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...';

    let formData = new FormData(document.getElementById("addToCartForm"));

    fetch("ajax_add_to_cart.php", {
      method: "POST",
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === "success") {
        // Update subtotal display
        const subtotalElements = document.querySelectorAll(".totalprice.col-6:last-child h6 b");
        if (subtotalElements.length > 0) {
          subtotalElements[subtotalElements.length - 1].textContent = "₱" + data.subtotal;
        }

        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById("cartModal"));
        if (modal) modal.hide();

        // Reload cart items
        loadCartItems();
      } else {
        alert("Error: " + data.message);
      }

      isAddingToCart = false;
      addCartBtn.disabled = false;
      addCartBtn.innerHTML = 'Add to Cart';
    })
    .catch(error => {
      console.error('Error:', error);
      alert("Network error. Please try again.");

      isAddingToCart = false;
      addCartBtn.disabled = false;
      addCartBtn.innerHTML = 'Add to Cart';
    });
  });

  /* =========================================================
     DELETE CART ITEM - AJAX VERSION
  ========================================================= */
  document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-circle')) {
      const removeBtn = e.target.closest('.remove-circle');
      const deleteId = removeBtn.getAttribute('data-id');
      
      if (!deleteId) return;
      
      if (confirm('Are you sure you want to remove this item?')) {
        removeBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';
        removeBtn.disabled = true;
        
        fetch("ajax_delete_cart.php", {
          method: "POST",
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `delete_id=${deleteId}`
        })
        .then(res => res.json())
        .then(data => {
          if (data.status === "success") {
            // Update subtotal
            const subtotalElements = document.querySelectorAll(".totalprice.col-6:last-child h6 b");
            if (subtotalElements.length > 0) {
              subtotalElements[subtotalElements.length - 1].textContent = "₱" + data.subtotal;
            }
            
            // Reload cart items
            loadCartItems();
          } else {
            alert("Error: " + data.message);
            removeBtn.innerHTML = '<i class="bi bi-x"></i>';
            removeBtn.disabled = false;
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert("Network error. Please try again.");
          removeBtn.innerHTML = '<i class="bi bi-x"></i>';
          removeBtn.disabled = false;
        });
      }
    }
  });

  /* =========================================================
     LOAD CART ITEMS (AJAX)
  ========================================================= */
  function loadCartItems() {
    fetch("cart_items.php")
      .then(res => res.text())
      .then(html => {
        document.getElementById("cartAccordion").innerHTML = html;
      })
      .catch(error => console.error('Error loading cart items:', error));
  }

  /* =========================================================
     RESET STATE WHEN MODAL CLOSES
  ========================================================= */
  cartModal.addEventListener('hide.bs.modal', function() {
    isAddingToCart = false;
    const addCartBtn = document.getElementById('addCartBtn');
    if (addCartBtn) {
      addCartBtn.disabled = false;
      addCartBtn.innerHTML = 'Add to Cart';
    }
  });

  /* =========================================================
     INITIAL LOAD - Load cart items on page load
  ========================================================= */
  loadCartItems();
});