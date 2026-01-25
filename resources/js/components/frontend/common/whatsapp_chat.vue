<template>
  <div class="whatsapp-float">
    <a
      href="https://wa.me/8801894971070"
      target="_blank"
      class="whatsapp-button"
      title="Chat on WhatsApp"
    >
      <svg width="35" height="35" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path fill="#ffffff" d="M20.52 3.48A11.82 11.82 0 0012.04 0C5.4 0 0 5.4 0 12.04c0 2.12.56 4.2 1.64 6.04L0 24l6.12-1.6a11.93 11.93 0 005.92 1.52h.04c6.64 0 12.04-5.4 12.04-12.04 0-3.2-1.24-6.2-3.6-8.4zm-8.48 18.2h-.04a9.88 9.88 0 01-5.04-1.4l-.36-.2-3.64.96.96-3.52-.24-.36a9.9 9.9 0 01-1.56-5.32C2.12 6.6 6.6 2.12 12.04 2.12c2.64 0 5.12 1.04 7 2.92a9.82 9.82 0 012.92 7c0 5.44-4.48 9.92-9.92 9.92zm5.44-7.4c-.28-.12-1.64-.8-1.88-.88-.24-.08-.44-.12-.64.12-.2.28-.72.88-.88 1.04-.16.16-.32.2-.6.08-.28-.12-1.2-.44-2.28-1.4-.84-.72-1.4-1.6-1.56-1.88-.16-.28-.04-.44.08-.56.12-.12.28-.32.4-.48.12-.16.16-.28.24-.48.08-.2.04-.36-.04-.48-.08-.12-.64-1.52-.88-2.08-.24-.6-.48-.52-.64-.52-.16 0-.36 0-.56 0-.2 0-.48.08-.72.36-.24.28-.96.92-.96 2.24s.96 2.6 1.08 2.8c.12.2 1.88 2.88 4.56 4.04.64.28 1.12.44 1.52.56.64.2 1.24.16 1.72.08.52-.08 1.64-.68 1.88-1.32.24-.64.24-1.2.16-1.32-.08-.12-.24-.2-.52-.32z"/>
      </svg>
    </a>
  </div>
</template>

<script>
export default {
  name: "WhatsAppChat"
};
</script>

<style scoped>
.whatsapp-float {
  position: fixed;
  bottom: 120px;
  right: 170px;
  z-index: 9999;
}

.whatsapp-button {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 60px;
  height: 60px;
  background-color: #25D366;
  border-radius: 50%;
  transition: all 0.3s ease;
  text-decoration: none;
}

.whatsapp-button:hover {
  background-color: #128C7E;
  transform: scale(1.1);
}

.whatsapp-button svg {
  width: 35px;
  height: 35px;
}

/* Mobile responsive */
@media (max-width: 768px) {
  .whatsapp-float {
    bottom: 120px;
    right: 90px;
  }

  .whatsapp-button {
    width: 50px;
    height: 50px;
  }

  .whatsapp-button svg {
    width: 30px;
    height: 30px;
  }
}
</style>

