// marketplace.js

const products = [
  { id: 1, name: 'Projeto 1', price: 120 },
  { id: 2, name: 'Projeto 2', price: 130 },
  { id: 3, name: 'Projeto 3', price: 150 },
  { id: 4, name: 'Projeto 4', price: 170 },
  { id: 5, name: 'Projeto 5', price: 200 }
];

const cart = [];
const productsContainer = document.getElementById('products-container');
const cartItemsContainer = document.getElementById('cart-items');
const cartTotalEl = document.getElementById('cart-total');
const checkoutBtn = document.getElementById('checkout-btn');

// Renderiza produtos no marketplace
function renderProducts() {
  productsContainer.innerHTML = ''; // Limpa antes de renderizar
  products.forEach(product => {
    const col = document.createElement('div');
    col.className = 'col-md-4';
    col.innerHTML = `
      <div class="card text-center p-3">
        <div class="bg-light mb-3 d-flex align-items-center justify-content-center" style="height:150px;">
          Imagem ${product.id}
        </div>
        <h5 class="card-title">${product.name}</h5>
        <p class="card-text">Preço: $${product.price}</p>
        <button class="btn btn-laranja w-100" onclick="addToCart(${product.id})">Adicionar ao Carrinho</button>
      </div>
    `;
    productsContainer.appendChild(col);
  });
}

// Adiciona item ao carrinho
function addToCart(id) {
  const product = products.find(p => p.id === id);
  if (!product) return;

  cart.push(product);
  renderCart();

  // Evento de tracking do Google Analytics
  if (typeof gtag === 'function') {
    gtag('event', 'add_to_cart', {
      item_name: product.name,
      value: product.price,
      currency: 'USD'
    });
  }
}

// Remove item do carrinho
function removeFromCart(index) {
  if (index < 0 || index >= cart.length) return;
  cart.splice(index, 1);
  renderCart();
}

// Renderiza o carrinho
function renderCart() {
  cartItemsContainer.innerHTML = '';
  cart.forEach((item, index) => {
    const div = document.createElement('div');
    div.className = 'cart-item d-flex justify-content-between align-items-center mb-2';
    div.innerHTML = `
      <span>${item.name} - $${item.price}</span>
      <button class="btn btn-sm btn-danger" onclick="removeFromCart(${index})">&times;</button>
    `;
    cartItemsContainer.appendChild(div);
  });

  const total = cart.reduce((sum, item) => sum + item.price, 0);
  cartTotalEl.textContent = total;

  // Ativa ou desativa botão de checkout
  checkoutBtn.disabled = total === 0;
}

// Checkout via PayPal (redirecionamento)
checkoutBtn.addEventListener('click', () => {
  const total = cart.reduce((sum, item) => sum + item.price, 0);
  if (total === 0) return alert("O carrinho está vazio!");

  const paypalLink = `https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=SEU_EMAIL_PAYPAL&currency_code=USD&amount=${total}`;
  window.location.href = paypalLink;
});

// Inicializa marketplace
document.addEventListener('DOMContentLoaded', renderProducts);








