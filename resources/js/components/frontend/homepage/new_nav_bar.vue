<template>
  <div>
  <div class="header-area">
    <!-- Top Header -->

    <div class="top-header py-4">
      <div class="container">
        <div class="row d-flex align-items-center">

          <div class="logo col col-lg-2 col-md-2 col-sm-6">
            <div class="logo-area">
              <div class="logo-img text-white fs-2">dollbear</div>
            </div>
          </div>

          <div class="search col col-lg-4 col-md-4 col-sm-6  ">
            <div class="search-area">
              <input type="text" class="search-box" placeholder="Search">
              <button class="search-btn"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
  <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
</svg></button>
            </div>
          </div>

          <div class="contact-info col col-lg-3 col-md-3 col-sm-6 ">
            <div class="chat-call d-flex align-items-center justify-content-end">
              <span class="user-btn text-white"><svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-telephone-fill" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M1.885.511a1.745 1.745 0 0 1 2.61.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"/>
</svg></span>
              <div class="call text-white d-grid fs-6 fs-md-6">
                <span><a href="#" class="text-white">Live chat or:</a></span>
                <span ><a href="tel:+0967820290" class="text-white">+0967820290</a></span>
              </div>
            </div>
          </div>

          <div class="woo-info col col-lg-3 col-md-3 col-sm-6 ">
            <div class="cart-area d-flex justify-content-end">
              <div class="offer px-2 px-sm-1">
                <a href="#" class="text-decoration-none"><span class="cart-btn text-white d-grid"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-shop" viewBox="0 0 16 16">
  <path d="M2.97 1.35A1 1 0 0 1 3.73 1h8.54a1 1 0 0 1 .76.35l2.609 3.044A1.5 1.5 0 0 1 16 5.37v.255a2.375 2.375 0 0 1-4.25 1.458A2.37 2.37 0 0 1 9.875 8 2.37 2.37 0 0 1 8 7.083 2.37 2.37 0 0 1 6.125 8a2.37 2.37 0 0 1-1.875-.917A2.375 2.375 0 0 1 0 5.625V5.37a1.5 1.5 0 0 1 .361-.976zm1.78 4.275a1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0 1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0 1.375 1.375 0 1 0 2.75 0V5.37a.5.5 0 0 0-.12-.325L12.27 2H3.73L1.12 5.045A.5.5 0 0 0 1 5.37v.255a1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0M1.5 8.5A.5.5 0 0 1 2 9v6h1v-5a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1v5h6V9a.5.5 0 0 1 1 0v6h.5a.5.5 0 0 1 0 1H.5a.5.5 0 0 1 0-1H1V9a.5.5 0 0 1 .5-.5M4 15h3v-5H4zm5-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1zm3 0h-2v3h2z"/>
</svg>Offers</span></a>
              </div>
              <div class="cart px-2 px-sm-1">
                <a href="#" class="text-decoration-none"><span class="cart-btn text-white d-grid"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-cart2" viewBox="0 0 16 16">
  <path d="M0 2.5A.5.5 0 0 1 .5 2H2a.5.5 0 0 1 .485.379L2.89 4H14.5a.5.5 0 0 1 .485.621l-1.5 6A.5.5 0 0 1 13 11H4a.5.5 0 0 1-.485-.379L1.61 3H.5a.5.5 0 0 1-.5-.5M3.14 5l1.25 5h8.22l1.25-5zM5 13a1 1 0 1 0 0 2 1 1 0 0 0 0-2m-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0m9-1a1 1 0 1 0 0 2 1 1 0 0 0 0-2m-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0"/>
</svg>Cart</span></a>
              </div>
              <div class="register px-2 px-sm-1">
                <a href="#" class="text-decoration-none"><span class="cart-btn text-white d-grid"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16">
  <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
</svg>Register</span></a>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- Top Header End -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
      <div class="container">
        <!-- <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#mobile_nav"
          aria-controls="mobile_nav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button> -->
        <div class="collapse navbar-collapse" id="mobile_nav">
          <ul class="navbar-nav mr-auto mt-2 mt-lg-0 float-md-right">
          </ul>
          <ul class="navbar-nav navbar-light">
           
           
            <li class="nav-item dropdown megamenu-li dmenu">
              <a class="nav-link dropdown-toggle" href="" id="dropdown01" data-toggle="dropdown" aria-haspopup="true"
                aria-expanded="false">Cables</a>
              <div class="dropdown-menu megamenu sm-menu border-top" aria-labelledby="dropdown01">
                <div class="row">
                  <div class="col-sm-6 col-lg-3 border-right mb-4">
                    <h6>Charger</h6>
                    <a class="dropdown-item" href="#"><i class="fab fa-magento"></i> Magento Development</a>
                    <a class="dropdown-item" href="#"><i class="fab fa-magento"></i> Magento 2 Migration</a>
                    <a class="dropdown-item" href="#"><i class="fab fa-magento"></i> Odoo ERP</a>
                    <a class="dropdown-item" href="#"><i class="fab fa-magento"></i> Mobile Commerce</a>
                    <a class="dropdown-item" href="#"><i class="fab fa-magento"></i> CRM for Commerce</a>
                  </div>
                  <div class="col-sm-6 col-lg-3 border-right mb-4">
                    <h6>LAMP Technology</h6>
                    <a class="dropdown-item" href="#"><i class="fab fa-php"></i> PHP Website Development</a>
                    <a class="dropdown-item" href="#"><i class="fas fa-circle"></i> Phalcon Development</a>
                    <a class="dropdown-item" href="#"><i class="fab fa-laravel"></i> Laravel Development</a>
                    <a class="dropdown-item" href="#"><i class="fab fa-wordpress-simple"></i> WordPress Development</a>
                    <a class="dropdown-item" href="#"><i class="fab fa-php"></i> Symfony Development</a>
                  </div>
                  <div class="col-sm-6 col-lg-3 border-right mb-4">
                    <h6>Mobile</h6>
                    <a class="dropdown-item" href="#"><i class="fab fa-apple"></i> iPhone App Development</a>
                    <a class="dropdown-item" href="#"><i class="fab fa-android"></i> Android App Development</a>
                    <a class="dropdown-item" href="#"><i class="fas fa-mobile-alt"></i> Phone Gap App Development</a>
                    <a class="dropdown-item" href="#"><i class="fas fa-tablet-alt"></i> Hybrid App Development</a>
                    <a class="dropdown-item" href="#"><i class="fas fa-mobile-alt"></i> Ionic Development</a>
                    <a class="dropdown-item" href="#"><i class="fas fa-tablet-alt"></i> React Native Development</a>
                    <a class="dropdown-item" href="#"><i class="fas fa-mobile-alt"></i> Xamarin App Development</a>
                  </div>
                  <div class="col-sm-6 col-lg-3 mb-4">
                    <h6>Node.js & MongoDB</h6>
                    <a class="dropdown-item" href="#"><i class="fas fa-cubes"></i> Full Stack Development</a>
                    <a class="dropdown-item" href="#"><i class="fas fa-cube"></i> MEAN Stack</a>
                    <a class="dropdown-item" href="#"><i class="fab fa-angular"></i> AngularJS</a>
                    <a class="dropdown-item" href="#"><i class="fab fa-node-js"></i> Node.JS Development</a>
                    <a class="dropdown-item" href="#"><i class="fas fa-leaf fa-rotate-90"></i> MongoDB Development</a>
                  </div>
                </div>
                <div class="row">
                  <div class="col-sm-6 col-lg-3 border-right mb-4">
                    <h6>Microsoft Technology</h6>
                    <a class="dropdown-item" href="#"><i class="fab fa-windows"></i> Microsoft App Development</a>
                    <a class="dropdown-item" href="#"><i class="fab fa-windows"></i> MS Desktop App Development</a>
                    <a class="dropdown-item" href="#"><i class="fab fa-windows"></i> SharePoint Development</a>
                    <a class="dropdown-item" href="#"><i class="fab fa-windows"></i> ASP.NET Development</a>
                    <a class="dropdown-item" href="#"><i class="fab fa-windows"></i> CMS Development</a>
                  </div>
                  <div class="col-sm-6 col-lg-3 border-right mb-4">
                    <h6>Cloud Services</h6>
                    <a class="dropdown-item" href="#"><i class="fas fa-cloud"></i> DevOps</a>
                    <a class="dropdown-item" href="#"><i class="fab fa-amazon"></i> Amazon Web Services</a>
                    <a class="dropdown-item" href="#"><i class="fab fa-windows"></i> Azure Cloud Service</a>
                    <a class="dropdown-item" href="#"><i class="fab fa-google"></i> Google App Engine Services</a>
                  </div>
                  <div class="col-sm-6 col-lg-3 border-right mb-4">
                    <h6>Enterprise Services</h6>
                    <a class="dropdown-item" href="#"><i class="fas fa-laptop"></i> Augmented Reality</a>
                    <a class="dropdown-item" href="#"><i class="fas fa-laptop"></i> CRM</a>
                    <a class="dropdown-item" href="#"><i class="fab fa-buromobelexperte"></i> Enterprise Mobility
                      Services</a>
                    <a class="dropdown-item" href="#"><i class="fas fa-th-large"></i> Blockchain Services</a>
                    <a class="dropdown-item" href="#"><i class="fas fa-briefcase"></i> Business Intelligence</a>
                  </div>
                  <div class="col-sm-6 col-lg-3 mb-4">
                    <h6>Digital Marketing</h6>
                    <a class="dropdown-item" href="#"><i class="fas fa-laptop"></i> Digital Marketing</a>
                    <a class="dropdown-item" href="#"><i class="fas fa-laptop"></i> PPC Management Services</a>
                    <a class="dropdown-item" href="#"><i class="fas fa-laptop"></i> E-commerce SEO Services</a>
                    <a class="dropdown-item" href="#"><i class="fas fa-laptop"></i> Conversion Rate Optimization</a>
                  </div>
                </div>
              </div>
            </li>

            <li class="nav-item dropdown megamenu-li dmenu">
              <a class="nav-link dropdown-toggle" href="" id="dropdown01" data-toggle="dropdown" aria-haspopup="true"
                aria-expanded="false">Cables</a>
              <div class="dropdown-menu megamenu sm-menu border-top" aria-labelledby="dropdown01">
                <div class="row">
                  <div class="col-sm-6 col-lg-3 border-right mb-4">
                    <h6>Charger</h6>
                    <a class="dropdown-item" href="#"><i class="fab fa-magento"></i> Magento Development</a>
                    <a class="dropdown-item" href="#"><i class="fab fa-magento"></i> Magento 2 Migration</a>
                    <a class="dropdown-item" href="#"><i class="fab fa-magento"></i> Odoo ERP</a>
                    <a class="dropdown-item" href="#"><i class="fab fa-magento"></i> Mobile Commerce</a>
                    <a class="dropdown-item" href="#"><i class="fab fa-magento"></i> CRM for Commerce</a>
                  </div>
                  <div class="col-sm-6 col-lg-3 border-right mb-4">
                    <h6>LAMP Technology</h6>
                    <a class="dropdown-item" href="#"><i class="fab fa-php"></i> PHP Website Development</a>
                    <a class="dropdown-item" href="#"><i class="fas fa-circle"></i> Phalcon Development</a>
                    <a class="dropdown-item" href="#"><i class="fab fa-laravel"></i> Laravel Development</a>
                    <a class="dropdown-item" href="#"><i class="fab fa-wordpress-simple"></i> WordPress Development</a>
                    <a class="dropdown-item" href="#"><i class="fab fa-php"></i> Symfony Development</a>
                  </div>
                  <div class="col-sm-6 col-lg-3 border-right mb-4">
                    <h6>Mobile</h6>
                    <a class="dropdown-item" href="#"><i class="fab fa-apple"></i> iPhone App Development</a>
                    <a class="dropdown-item" href="#"><i class="fab fa-android"></i> Android App Development</a>
                    <a class="dropdown-item" href="#"><i class="fas fa-mobile-alt"></i> Phone Gap App Development</a>
                    <a class="dropdown-item" href="#"><i class="fas fa-tablet-alt"></i> Hybrid App Development</a>
                    <a class="dropdown-item" href="#"><i class="fas fa-mobile-alt"></i> Ionic Development</a>
                    <a class="dropdown-item" href="#"><i class="fas fa-tablet-alt"></i> React Native Development</a>
                    <a class="dropdown-item" href="#"><i class="fas fa-mobile-alt"></i> Xamarin App Development</a>
                  </div>
                  <div class="col-sm-6 col-lg-3 mb-4">
                    <h6>Node.js & MongoDB</h6>
                    <a class="dropdown-item" href="#"><i class="fas fa-cubes"></i> Full Stack Development</a>
                    <a class="dropdown-item" href="#"><i class="fas fa-cube"></i> MEAN Stack</a>
                    <a class="dropdown-item" href="#"><i class="fab fa-angular"></i> AngularJS</a>
                    <a class="dropdown-item" href="#"><i class="fab fa-node-js"></i> Node.JS Development</a>
                    <a class="dropdown-item" href="#"><i class="fas fa-leaf fa-rotate-90"></i> MongoDB Development</a>
                  </div>
                </div>
                <div class="row">
                  <div class="col-sm-6 col-lg-3 border-right mb-4">
                    <h6>Microsoft Technology</h6>
                    <a class="dropdown-item" href="#"><i class="fab fa-windows"></i> Microsoft App Development</a>
                    <a class="dropdown-item" href="#"><i class="fab fa-windows"></i> MS Desktop App Development</a>
                    <a class="dropdown-item" href="#"><i class="fab fa-windows"></i> SharePoint Development</a>
                    <a class="dropdown-item" href="#"><i class="fab fa-windows"></i> ASP.NET Development</a>
                    <a class="dropdown-item" href="#"><i class="fab fa-windows"></i> CMS Development</a>
                  </div>
                  <div class="col-sm-6 col-lg-3 border-right mb-4">
                    <h6>Cloud Services</h6>
                    <a class="dropdown-item" href="#"><i class="fas fa-cloud"></i> DevOps</a>
                    <a class="dropdown-item" href="#"><i class="fab fa-amazon"></i> Amazon Web Services</a>
                    <a class="dropdown-item" href="#"><i class="fab fa-windows"></i> Azure Cloud Service</a>
                    <a class="dropdown-item" href="#"><i class="fab fa-google"></i> Google App Engine Services</a>
                  </div>
                  <div class="col-sm-6 col-lg-3 border-right mb-4">
                    <h6>Enterprise Services</h6>
                    <a class="dropdown-item" href="#"><i class="fas fa-laptop"></i> Augmented Reality</a>
                    <a class="dropdown-item" href="#"><i class="fas fa-laptop"></i> CRM</a>
                    <a class="dropdown-item" href="#"><i class="fab fa-buromobelexperte"></i> Enterprise Mobility
                      Services</a>
                    <a class="dropdown-item" href="#"><i class="fas fa-th-large"></i> Blockchain Services</a>
                    <a class="dropdown-item" href="#"><i class="fas fa-briefcase"></i> Business Intelligence</a>
                  </div>
                  <div class="col-sm-6 col-lg-3 mb-4">
                    <h6>Digital Marketing</h6>
                    <a class="dropdown-item" href="#"><i class="fas fa-laptop"></i> Digital Marketing</a>
                    <a class="dropdown-item" href="#"><i class="fas fa-laptop"></i> PPC Management Services</a>
                    <a class="dropdown-item" href="#"><i class="fas fa-laptop"></i> E-commerce SEO Services</a>
                    <a class="dropdown-item" href="#"><i class="fas fa-laptop"></i> Conversion Rate Optimization</a>
                  </div>
                </div>
              </div>
            </li>
            
            <li class="nav-item"><a class="nav-link" href="#">Hubs and Docs</a></li>
            <li class="nav-item"><a class="nav-link" href="#">Gear</a></li>
            <li class="nav-item"><a class="nav-link" href="#">Support</a></li>

          </ul>
        </div>
      </div>
    </nav>

    

  </div>

 

</div>
  
</template>

<script>

import navbarScript from '../../../../js/navbar.js';

export default {
  mounted() {
    navbarScript();
  }
}
</script>


<style scoped>

.border-top {
   border-top: 0px solid #dee2e6!important;
}

.dropdown-menu.megamenu.sm-menu.border-top {
    background-color: #575757;
    color: #dddddd;
}

 /* Sticky Header */
 .header-area {
  position: sticky;
  top: 0;
  width: 100%;
  z-index: 9999;
}
 /* Sticky Header End*/

 
 /* Top Header */
 .top-header{
  /* height: 100px; */
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 20px;
  background: #0B0B0B;

}
.top-header .logo img{
  width: 100px;
}

input.search-box {
  background: transparent;
  border: 1px solid #8d8d8d !important;
  border-radius: 5px 0 0 5px;
  padding: 5px 5px 5px 10px;
  width: 90%;
}
button.search-btn {
  padding: 6px 10px;
  border: none;
  border-radius: 0 5px 5px 0;
  color: #fff;
  background: #16b5e3;
  margin-left: -6px;
}
/* Top Header End */



.navbar {
    background: #0000009e !important;
    padding-top: 0;
    padding-bottom: 0;
    box-shadow: 1px 3px 4px 0 #adadad33;
  }
  .navbar-light .navbar-brand {
    color: #ffffff;
  }
  .navbar-light .navbar-nav .nav-link {
    color: #ffffff;
  }
  .navbar-light .navbar-brand:focus,
  .navbar-light .navbar-brand:hover {
    color: #1ebdc2;
  }
  .navbar-light .navbar-nav .nav-link:focus,
  .navbar-light .navbar-nav .nav-link:hover {
    color: #fff;
  }
  .navbar-light .navbar-nav .nav-link {
    padding-top: 22px;
    padding-bottom: 22px;
    transition: 0.3s;
    padding-left: 12px;
    padding-right: 12px;
    font-size: 14px;
}
  .navbar-light .navbar-nav .nav-link:focus,
  .navbar-light .navbar-nav .nav-link:hover {
    background:#000;
    transition: 0.3s;
  }
  /* a.dropdown-item:focus, */
  a.dropdown-item:hover {
    color: #fff;
    font-weight: bold;
    text-decoration: none;
    background-color: #575757 !important;
  }
  .sm-menu {
    border-radius: 0px;
    border: 0px;
    top: 97%;
    box-shadow: rgba(173, 173, 173, 0.2) 1px 3px 4px 0px;
  }
  .dropdown-item {
    color: #dddddd;
    font-size: 14px;
  }
  .dropdown-item.active,
  .dropdown-item:active {
    color: #fff;
    text-decoration: none;
    /* background-color: #d1d2d3; */
  }
  .navbar-toggler {
    outline: none !important;
  }
  .navbar-tog {
    color: #1ebdc2;
  }
  .megamenu-li {
    position: static;
  }
  
  .megamenu {
    position: absolute;
    width: 100%;
    left: 0;
    right: 0;
    padding: 15px;
  }
  .megamenu h6 {
    margin-left: 21px;
  }
  .megamenu i {
    width: 20px;
  }
  


  /* Responsive */
@media all and (max-width: 768px){
  .top-header{
    flex-direction: column;
    align-items: flex-start;
    padding: 10px 20px;
    width: 100% ;
  }
  .top-header .logo{

    width: 50% !important;
  }
  .top-header .search{
    width: 50% !important;
  }
  .search .search-box {
    width: 80% !important;
}
  .top-header .contact-info{
    width: 50% !important;
  }
  span.user-btn i {
    font-size: 26px !important;
}
  .contact-info .call span {
    font-size: 14px;
}
  .top-header .woo-infospan.cart-btn {
    font-size: 12px;
}
.navbar{
  background: #fff !important;
  padding: 10px 0;
}
div#mobile_nav {
  background: #000;
}
}

@media all and (max-width: 425px){
  .top-header .row{
    justify-content: space-between !important;
  }
  .top-header .row .col {
    width: 50% !important;
    margin-bottom: 10px !important;
    display: contents;
}
  .top-header{
    flex-direction: column;
    align-items: flex-start;
    padding: 10px 20px;
    width: 100% ;
  }
  .top-header .logo{
    order: 1 !important;
    width: 50% !important;
  }
  .top-header .search{
    order: 3 !important;
    width: 50% !important;
  }
  .search-area {
    display: flex;
    justify-content: flex-end;
}
  .search .search-box {
    width: 70% !important;
}
span.user-btn i {
  padding-left: 0 !important;
}
  .top-header .contact-info{
    order: 2 !important;
    width: 50% !important;
  }
  .top-header .woo-info{
  order: 4 !important;
  width: 50% !important;
}
.logo-area,
.search-area{
  margin-bottom: 20px;
}
}


</style>