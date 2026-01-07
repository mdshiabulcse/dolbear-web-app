<template>

    <div>
  <!-- Navbar -->
  <div class="top-bar d-flex align-items-center justify-content-center">
      <p class="text-center mb-0">Want same day shopping?</p>
    </div>
    <!-- header -->
    <header class="header d-none d-lg-block">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-md-2">
            <a href="#"><img src="../../../../../public/images/img/logo-white.svg" alt="logo"></a>
          </div>
          <div class="col-md-4">
            <div class="input-group">
              <input type="text" class="form-control search-input" placeholder="Recipient's username" aria-label="Recipient's username" aria-describedby="basic-addon2">
              <button type="button" class="input-group-text search-input-btn " >
                <svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M14.8515 13.4937L10.3068 8.94896C11.012 8.03722 11.3935 6.92247 11.3935 5.74998C11.3935 4.34648 10.8458 3.03049 9.85527 2.03824C8.86477 1.046 7.54528 0.5 6.14353 0.5C4.74179 0.5 3.42229 1.04775 2.4318 2.03824C1.43955 3.02874 0.893555 4.34648 0.893555 5.74998C0.893555 7.15172 1.4413 8.47121 2.4318 9.46171C3.42229 10.454 4.74004 11 6.14353 11C7.31603 11 8.42902 10.6185 9.34077 9.91496L13.8855 14.4579C13.8988 14.4713 13.9146 14.4819 13.9321 14.4891C13.9495 14.4963 13.9681 14.5 13.987 14.5C14.0058 14.5 14.0245 14.4963 14.0419 14.4891C14.0593 14.4819 14.0752 14.4713 14.0885 14.4579L14.8515 13.6967C14.8648 13.6834 14.8754 13.6675 14.8826 13.6501C14.8898 13.6327 14.8936 13.614 14.8936 13.5952C14.8936 13.5763 14.8898 13.5577 14.8826 13.5403C14.8754 13.5228 14.8648 13.507 14.8515 13.4937ZM8.91552 8.52197C8.17352 9.26221 7.19003 9.66996 6.14353 9.66996C5.09704 9.66996 4.11354 9.26221 3.37154 8.52197C2.6313 7.77997 2.22355 6.79647 2.22355 5.74998C2.22355 4.70348 2.6313 3.71824 3.37154 2.97799C4.11354 2.23774 5.09704 1.82999 6.14353 1.82999C7.19003 1.82999 8.17527 2.23599 8.91552 2.97799C9.65577 3.71999 10.0635 4.70348 10.0635 5.74998C10.0635 6.79647 9.65577 7.78172 8.91552 8.52197Z" fill="white"/>
                  </svg>
              </button>
            </div>
          </div>
          <div class="col-md-3">
            <div class="d-flex align-items-center justify-content-center">
              <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M23.3418 24.5C20.8138 24.5 18.2218 23.867 15.5658 22.601C12.9098 21.334 10.4428 19.5565 8.16478 17.2685C5.88478 14.9795 4.11178 12.512 2.84578 9.866C1.57978 7.22 0.946777 4.633 0.946777 2.105C0.946777 1.647 1.09678 1.2655 1.39678 0.9605C1.69678 0.6535 2.07178 0.5 2.52178 0.5H6.23128C6.63928 0.5 6.99528 0.6285 7.29928 0.8855C7.60328 1.1425 7.80878 1.4715 7.91578 1.8725L8.65978 5.45C8.72978 5.87 8.71728 6.2365 8.62228 6.5495C8.52828 6.8625 8.36228 7.119 8.12428 7.319L4.83178 10.388C5.44778 11.506 6.12828 12.544 6.87328 13.502C7.61828 14.461 8.41328 15.3695 9.25828 16.2275C10.1283 17.0975 11.0653 17.9075 12.0693 18.6575C13.0733 19.4065 14.1753 20.1135 15.3753 20.7785L18.5838 17.513C18.8278 17.25 19.1023 17.0765 19.4073 16.9925C19.7123 16.9095 20.0493 16.893 20.4183 16.943L23.5743 17.588C23.9813 17.688 24.3118 17.893 24.5658 18.203C24.8198 18.514 24.9468 18.8705 24.9468 19.2725V22.925C24.9468 23.375 24.7938 23.75 24.4878 24.05C24.1828 24.35 23.7998 24.5 23.3418 24.5ZM4.12678 8.981L7.00678 6.332C7.10278 6.256 7.16478 6.1505 7.19278 6.0155C7.22278 5.8805 7.21778 5.7555 7.17778 5.6405L6.51328 2.4605C6.47428 2.3075 6.40678 2.1925 6.31078 2.1155C6.21478 2.0385 6.09028 2 5.93728 2H2.85928C2.74428 2 2.64828 2.0385 2.57128 2.1155C2.49428 2.1925 2.45578 2.2885 2.45578 2.4035C2.48478 3.4285 2.64478 4.4985 2.93578 5.6135C3.22878 6.7295 3.62478 7.852 4.12678 8.981ZM16.8033 21.482C17.8173 21.984 18.8973 22.3555 20.0433 22.5965C21.1913 22.8365 22.1913 22.965 23.0433 22.982C23.1583 22.982 23.2543 22.944 23.3313 22.868C23.4083 22.791 23.4468 22.6945 23.4468 22.5785V19.568C23.4468 19.414 23.4083 19.289 23.3313 19.193C23.2543 19.096 23.1393 19.0285 22.9863 18.9905L20.2113 18.422C20.0953 18.384 19.9938 18.379 19.9068 18.407C19.8208 18.437 19.7298 18.4995 19.6338 18.5945L16.8033 21.482Z" fill="#8F8F8F"/>
                </svg>
                <div class="ms-2 live-chat">
                  <p class="mb-0">Live chat or:</p>
                  <p class="mb-0 number">+0967820290</p>
                </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="d-flex align-items-center header-group justify-content-between">
              <a href="#" class="item text-center me-2">
                <svg width="24" height="25" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M23.9957 21.432C23.9845 20.7028 23.6651 20.1421 23.0466 19.7625C22.9118 19.6793 22.8831 19.5898 22.8831 19.4486C22.8866 17.1464 22.8831 14.8441 22.8915 12.5419C22.8922 12.4202 22.9537 12.2734 23.0348 12.1811C23.4443 11.7134 23.8098 11.1981 23.8741 10.5829C23.9677 9.68661 23.9551 8.77913 23.9852 7.87585C23.9866 7.82621 23.9649 7.77448 23.9489 7.72554C23.3122 5.8183 22.6749 3.91177 22.0375 2.00523C21.6916 0.969118 21.0451 0.504195 19.9486 0.503496C17.1602 0.503496 14.3718 0.504195 11.5834 0.503496C9.05571 0.502797 6.52867 0.5 4.00164 0.5C2.98412 0.5 2.32091 0.974012 1.99804 1.93462C1.34951 3.86423 0.703778 5.79523 0.060139 7.72694C0.0251967 7.83251 0.000736983 7.94786 3.81352e-05 8.05833C-0.000660713 8.61623 0.00842431 9.17414 0.0126174 9.73205C0.0210036 10.6815 0.357848 11.4974 0.994498 12.1986C1.06648 12.2783 1.11819 12.409 1.11819 12.516C1.12518 14.8273 1.12169 17.138 1.12798 19.4486C1.12798 19.6101 1.07417 19.694 0.93929 19.7758C0.469664 20.061 0.121638 20.4749 0.0699229 21.0188C0.00842431 21.6641 -0.0230238 22.3332 0.0762125 22.9694C0.225766 23.9244 1.03713 24.4963 2.06723 24.497C5.37138 24.4998 8.67484 24.4977 11.9783 24.4977C13.5647 24.4984 15.1511 24.4984 16.7381 24.4977C18.4902 24.4977 20.2422 24.504 21.9942 24.4956C23.1661 24.49 23.9789 23.6735 23.9957 22.5045C24.0013 22.1472 24.0006 21.7893 23.9957 21.432ZM18.4468 2.17792C18.9779 2.17792 19.5098 2.18141 20.0409 2.17652C20.217 2.17512 20.347 2.22686 20.4064 2.40444C20.9242 3.9642 21.4428 5.52467 21.9599 7.08513C21.9669 7.10681 21.9606 7.13337 21.9606 7.16693H18.2987C18.0226 5.51068 17.7494 3.86912 17.4677 2.17792H18.4468ZM16.1861 13.2794C16.1854 13.1172 16.2448 13.0487 16.3796 12.9627C16.8017 12.6929 17.2043 12.3922 17.6306 12.0923C18.5607 13.1103 19.7607 13.5269 21.2003 13.2508V19.4458H16.1895V17.491C16.1895 16.0872 16.1923 14.6833 16.1861 13.2794ZM12.8553 2.1919H15.7528C16.0288 3.85024 16.3028 5.4981 16.5802 7.16763H12.8553V2.1919ZM16.7186 8.90567C16.7186 9.2867 16.743 9.64466 16.7137 9.99912C16.641 10.8674 15.815 11.6148 14.9072 11.6603C13.9511 11.7078 13.0741 11.0646 12.9015 10.1438C12.8267 9.74673 12.8442 9.33284 12.819 8.90567H16.7186ZM8.25483 2.19749H11.1543V7.16204H7.42949C7.70553 5.5002 7.97878 3.85863 8.25483 2.19749ZM7.28623 8.89728H11.1306C11.2697 10.0005 11.118 10.8667 10.155 11.4149C8.8796 12.1406 7.2988 11.2205 7.28623 9.75932C7.28413 9.48106 7.28623 9.20281 7.28623 8.89728ZM7.25338 18.0853C7.26526 17.3617 7.25687 16.6381 7.25757 15.9145V12.7963C9.02915 13.7157 10.6065 13.471 12.016 12.049C12.6722 12.7943 13.5004 13.2284 14.5039 13.3312V19.4535H2.8129V13.2564C3.94154 13.4137 4.08131 13.392 5.58313 12.8096V13.1207C5.58313 14.7427 5.58174 16.364 5.58453 17.986C5.58523 18.446 5.81585 18.7788 6.19532 18.8858C6.73064 19.0368 7.2436 18.6586 7.25338 18.0853ZM2.09728 6.92853C2.58298 5.46804 3.07427 4.00895 3.55088 2.54496C3.63684 2.27999 3.76473 2.16603 4.05336 2.17233C4.78435 2.1898 5.51674 2.17792 6.24844 2.17862C6.3337 2.17862 6.41895 2.18561 6.53846 2.1912C6.26171 3.85584 5.98916 5.4967 5.71242 7.16064H2.03089C2.05465 7.07674 2.07282 7.00194 2.09728 6.92853ZM1.68915 8.90148H5.59082C5.56916 9.33774 5.602 9.76351 5.51744 10.1648C5.31687 11.1219 4.40767 11.7525 3.44745 11.6561C2.46697 11.5575 1.7234 10.7626 1.69055 9.7712C1.68147 9.48595 1.68915 9.20071 1.68915 8.90148ZM21.9816 22.8233C21.9117 22.8212 21.8418 22.8233 21.7719 22.8233H2.23635C1.69614 22.8233 1.68636 22.8135 1.68636 22.2605C1.68636 21.9983 1.68776 21.7369 1.68706 21.4754C1.68636 21.2726 1.7821 21.1664 1.98826 21.1678C2.07562 21.1685 2.16297 21.1671 2.25033 21.1671H21.8376C22.2989 21.1671 22.3226 21.1915 22.3226 21.6593C22.3233 21.9291 22.3191 22.1997 22.324 22.4702C22.3282 22.708 22.2255 22.831 21.9816 22.8233ZM20.9326 11.5722C19.7006 11.9742 18.4419 11.0352 18.4363 9.74463C18.4349 9.46778 18.4356 9.19162 18.4356 8.89589H22.2828C22.4631 10.1424 22.1745 11.1667 20.9326 11.5722Z" fill="white"/>
                  <path d="M9.7642 18.0855C10.3764 18.0911 10.8991 17.5668 10.89 16.955C10.8817 16.344 10.3778 15.849 9.7677 15.8511C9.15621 15.8532 8.65793 16.3524 8.65723 16.9648C8.65653 17.5737 9.15551 18.0799 9.7642 18.0855Z" fill="#16B5E3"/>
                  </svg>
                  <p class="mb-0">Offers</p>
              </a>
              <a href="" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight" class="item text-center me-2">
                <svg width="28" height="27" viewBox="0 0 28 27" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M8.35254 12.7941V7.14706C8.35254 5.64937 8.9475 4.21301 10.0065 3.15399C11.0656 2.09496 12.5019 1.5 13.9996 1.5C15.4973 1.5 16.9336 2.09496 17.9927 3.15399C19.0517 4.21301 19.6467 5.64937 19.6467 7.14706V12.7941" stroke="white" stroke-width="2" stroke-linecap="round"/>
                  <path d="M2.27385 13.7369C2.47855 11.2791 2.58161 10.0508 3.39196 9.30401C4.20232 8.55859 5.4362 8.55859 7.90255 8.55859H20.0988C22.5637 8.55859 23.7976 8.55859 24.608 9.30401C25.4183 10.0494 25.5214 11.2791 25.7261 13.7369L26.4517 22.4419C26.5703 23.872 26.6296 24.5878 26.2117 25.0438C25.791 25.4998 25.0738 25.4998 23.6367 25.4998H4.36326C2.92749 25.4998 2.20891 25.4998 1.78961 25.0438C1.37032 24.5878 1.42961 23.872 1.54961 22.4419L2.27385 13.7369Z" stroke="white" stroke-width="2"/>
                  </svg>
                  <p class="mb-0">Cart</p>
              </a>
              <a href="#" class="item text-center">
                <svg width="28" height="25" viewBox="0 0 28 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M27.182 23.0396C25.2313 19.8834 22.2252 17.6201 18.7171 16.5472C20.4524 15.5804 21.8006 14.1072 22.5547 12.3538C23.3088 10.6004 23.427 8.66383 22.8913 6.84145C22.3556 5.01906 21.1956 3.41164 19.5893 2.26604C17.983 1.12044 16.0193 0.5 13.9998 0.5C11.9803 0.5 10.0167 1.12044 8.41039 2.26604C6.80412 3.41164 5.64405 5.01906 5.10834 6.84145C4.57263 8.66383 4.69091 10.6004 5.445 12.3538C6.19909 14.1072 7.5473 15.5804 9.28259 16.5472C5.77444 17.6189 2.76837 19.8822 0.817687 23.0396C0.746152 23.1488 0.698703 23.2703 0.67814 23.3969C0.657578 23.5235 0.664317 23.6527 0.697962 23.7768C0.731606 23.9009 0.791474 24.0175 0.874032 24.1196C0.956591 24.2218 1.06017 24.3074 1.17865 24.3714C1.29714 24.4355 1.42813 24.4767 1.5639 24.4926C1.69967 24.5085 1.83747 24.4988 1.96917 24.464C2.10086 24.4293 2.22379 24.3702 2.3307 24.2903C2.4376 24.2103 2.52632 24.1112 2.59162 23.9986C5.00467 20.0955 9.26979 17.7652 13.9998 17.7652C18.7299 17.7652 22.995 20.0955 25.4081 23.9986C25.4734 24.1112 25.5621 24.2103 25.669 24.2903C25.7759 24.3702 25.8988 24.4293 26.0305 24.464C26.1622 24.4988 26.3 24.5085 26.4358 24.4926C26.5715 24.4767 26.7025 24.4355 26.821 24.3714C26.9395 24.3074 27.0431 24.2218 27.1256 24.1196C27.2082 24.0175 27.2681 23.9009 27.3017 23.7768C27.3354 23.6527 27.3421 23.5235 27.3215 23.3969C27.301 23.2703 27.2535 23.1488 27.182 23.0396ZM6.82727 9.13422C6.82727 7.80652 7.24793 6.50863 8.03607 5.40469C8.8242 4.30075 9.9444 3.44033 11.255 2.93224C12.5656 2.42416 14.0078 2.29122 15.3991 2.55024C16.7905 2.80926 18.0685 3.44861 19.0716 4.38743C20.0747 5.32626 20.7578 6.52239 21.0346 7.82458C21.3113 9.12677 21.1693 10.4765 20.6264 11.7032C20.0835 12.9298 19.1642 13.9782 17.9847 14.7158C16.8052 15.4535 15.4184 15.8472 13.9998 15.8472C12.0982 15.8453 10.275 15.1374 8.93031 13.8789C7.58564 12.6204 6.82931 10.914 6.82727 9.13422Z" fill="white"/>
                  </svg>
                  <p class="mb-0">Register</p>
              </a>
            </div>
          </div>
        </div>
      </div>
    </header>
    <!-- cart -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
      <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasRightLabel">SHOPPING CART</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close">X <span class="ms-3" style="font-size: 20px;">CLOSE</span></button>
      </div>
      <div class="offcanvas-body">
       <div class="cart-item">
        <div class="cart-item-img">
          <img src="img/mobile1.png" alt="cart">
        </div>
        <div class="cart-item-details">
          <p class="item-name mb-1">Anker iPhone 15 Series MagSafe Case - Clear, iPhone 15	</p>
          <p class="item-color mb-1">Corol: Pastel line</p>
          <p class="item-ram mb-1">RAM + ROM:8GB + 100GB</p>
          <!-- input -->
          <div class="cart-item-details-btn">
            <input type="button" value="-" class="minus">
            <input type="number" value="" inputmode="numeric" min="0" class="cart-item-details-btn-quantity" id="quantityInput">
            <input type="button" value="+" class="plus">
          </div>
          <div class="cart-item-details-quantity mt-2">
            <p class="mb-0"><span class="item-qnt">1 x</span> $6767.56</p>
          </div>

        </div>
        <div class="cart-item-cancel">
          X
        </div>
       </div>
       <div class="cart-item">
        <div class="cart-item-img">
          <img src="img/mobile1.png" alt="cart">
        </div>
        <div class="cart-item-details">
          <p class="item-name mb-1">Anker iPhone 15 Series MagSafe Case - Clear, iPhone 15	</p>
          <p class="item-color mb-1">Corol: Pastel line</p>
          <p class="item-ram mb-1">RAM + ROM:8GB + 100GB</p>
          <!-- input -->
          <div class="cart-item-details-btn">
            <input type="button" value="-" class="minus">
            <input type="number" value="" inputmode="numeric" min="0" class="cart-item-details-btn-quantity" id="quantityInput">
            <input type="button" value="+" class="plus">
          </div>
          <div class="cart-item-details-quantity mt-2">
            <p class="mb-0"><span class="item-qnt">1 x</span> $6767.56</p>
          </div>

        </div>
        <div class="cart-item-cancel">
          X
        </div>
       </div>
       <div class="cart-item">
        <div class="cart-item-img">
          <img src="img/mobile1.png" alt="cart">
        </div>
        <div class="cart-item-details">
          <p class="item-name mb-1">Anker iPhone 15 Series MagSafe Case - Clear, iPhone 15	</p>
          <p class="item-color mb-1">Corol: Pastel line</p>
          <p class="item-ram mb-1">RAM + ROM:8GB + 100GB</p>
          <!-- input -->
          <div class="cart-item-details-btn">
            <input type="button" value="-" class="minus">
            <input type="number" value="" inputmode="numeric" min="0" class="cart-item-details-btn-quantity" id="quantityInput">
            <input type="button" value="+" class="plus">
          </div>
          <div class="cart-item-details-quantity mt-2">
            <p class="mb-0"><span class="item-qnt">1 x</span> $6767.56</p>
          </div>

        </div>
        <div class="cart-item-cancel">
          X
        </div>
       </div>
       <div class="cart-item">
        <div class="cart-item-img">
          <img src="img/mobile1.png" alt="cart">
        </div>
        <div class="cart-item-details">
          <p class="item-name mb-1">Anker iPhone 15 Series MagSafe Case - Clear, iPhone 15	</p>
          <p class="item-color mb-1">Corol: Pastel line</p>
          <p class="item-ram mb-1">RAM + ROM:8GB + 100GB</p>
          <!-- input -->
          <div class="cart-item-details-btn">
            <input type="button" value="-" class="minus">
            <input type="number" value="" inputmode="numeric" min="0" class="cart-item-details-btn-quantity" id="quantityInput">
            <input type="button" value="+" class="plus">
          </div>
          <div class="cart-item-details-quantity mt-2">
            <p class="mb-0"><span class="item-qnt">1 x</span> $6767.56</p>
          </div>

        </div>
        <div class="cart-item-cancel">
          X
        </div>
       </div>
       <div class="cart-item">
        <div class="cart-item-img">
          <img src="img/mobile1.png" alt="cart">
        </div>
        <div class="cart-item-details">
          <p class="item-name mb-1">Anker iPhone 15 Series MagSafe Case - Clear, iPhone 15	</p>
          <p class="item-color mb-1">Corol: Pastel line</p>
          <p class="item-ram mb-1">RAM + ROM:8GB + 100GB</p>
          <!-- input -->
          <div class="cart-item-details-btn">
            <input type="button" value="-" class="minus">
            <input type="number" value="" inputmode="numeric" min="0" class="cart-item-details-btn-quantity" id="quantityInput">
            <input type="button" value="+" class="plus">
          </div>
          <div class="cart-item-details-quantity mt-2">
            <p class="mb-0"><span class="item-qnt">1 x</span> $6767.56</p>
          </div>

        </div>
        <div class="cart-item-cancel">
          X
        </div>
       </div>
      </div>
      <div class="offcanvas-footer">
        <div class="subtotals d-flex align-items-center justify-content-between">
          <p><strong>SUBTOTAL:</strong></p>
          <p><strong>৳ 89,130.00</strong></p>
        </div>
        <button class="view-cart">View Cart</button>
        <button class="checkout">CHEACHOUT</button>
      </div>
    </div>
    <!-- navbar -->

    <nav class="navbar">
      <div class="container">
        <div class="row v-center ms-auto ms-md-0 w-100">
          <!-- menu start here -->
          <div class="header-item item-center">
            <div class="menu-overlay"></div>
            <nav class="menu">
              <div class="mobile-menu-head">
                <div class="go-back">
                  <svg height="48" stroke="white" viewBox="0 0 9 48" width="9" xmlns="http://www.w3.org/2000/svg"><path d="m1.5618 24.0621 6.5581-6.4238c.2368-.2319.2407-.6118.0088-.8486-.2324-.2373-.6123-.2407-.8486-.0088l-7 6.8569c-.1157.1138-.1807.2695-.1802.4316.001.1621.0674.3174.1846.4297l7 6.7241c.1162.1118.2661.1675.4155.1675.1577 0 .3149-.062.4326-.1846.2295-.2388.2222-.6187-.0171-.8481z"></path></svg>
                </div>
                <div class="current-menu-title"></div>
                <div class="mobile-menu-close text-light">&times;</div>
              </div>
              <ul class="menu-main p-0 mb-0">
                <li class="nav-item menu-item-has-children">
                  <a href="#">Brand </a>
                  <div class="sub-menu mega-menu mega-menu-column-4">
                    <div class="container row">
                    <div class="list-item col-md-3">
                      <h4 class="title">Men's Fashion</h4>
                      <ul>
                        <li><a href="#">T-Shirts</a></li>
                        <li><a href="#">Jeans</a></li>
                        <li><a href="#">Suit</a></li>
                        <li><a href="#">Kurta</a></li>
                        <li><a href="#">Watch</a></li>
                      </ul>
                    </div>
                    <div class="list-item col-md-3">
                      <h4 class="title">Women's Fashion</h4>
                      <ul>
                        <li><a href="#">Sarees</a></li>
                        <li><a href="#">Sandals</a></li>
                        <li><a href="#">Watchs</a></li>
                        <li><a href="#">Shoes</a></li>
                      </ul>
                    </div>
                    <div class="list-item col-md-3">
                      <h4 class="title">Home, Kitchen</h4>
                      <ul>
                        <li><a href="#">Kettle</a></li>
                        <li><a href="#">Toaster</a></li>
                        <li><a href="#">Dishwasher</a></li>
                        <li><a href="#">Microwave oven</a></li>
                        <li><a href="#">Pitcher</a></li>
                        <li><a href="#">Blender</a></li>
                        <li><a href="#">Colander</a></li>
                        <li><a href="#">Tureen</a></li>
                        <li><a href="#">Cookware</a></li>
                      </ul>
                    </div>
                  </div>
                  </div>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-items">Smartphone</a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-items">Headphone</a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-items">Smart  Watch</a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-items">Speaker</a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-items">Power Bank</a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-items">Cables</a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-items">Charger</a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-items">Cases & Protection            </a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-items">Hubs & Docks
                  </a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-items">Gear</a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-items">Support</a>
                </li>
              </ul>
            </nav>
          </div>
          <!-- menu end here -->
          <div class="header-item item-right d-flex align-items-center justify-content-between d-lg-none">
            <div class="left">
              <a href="#"><img src="../../../../../public/images/img/logo-white.svg" alt="logo"></a>
            </div>
            <!-- mobile menu trigger -->
            <div class="right d-flex align-items-center">
              <div class="d-flex mobile-serach-cart align-items-center me-3">
                <a href="#" class="mobile-serach-icon me-3" >
                  <svg width="16" height="15" viewBox="0 0 16 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15.3716 14.5L12.6494 11.7778M12.2605 5.94444C12.2605 6.65942 12.1196 7.36739 11.846 8.02794C11.5724 8.68849 11.1714 9.28868 10.6658 9.79425C10.1603 10.2998 9.56008 10.7008 8.89953 10.9745C8.23898 11.2481 7.531 11.3889 6.81603 11.3889C6.10105 11.3889 5.39308 11.2481 4.73253 10.9745C4.07198 10.7008 3.47179 10.2998 2.96622 9.79425C2.46066 9.28868 2.05962 8.68849 1.78602 8.02794C1.51241 7.36739 1.37158 6.65942 1.37158 5.94444C1.37158 4.50049 1.94519 3.11567 2.96622 2.09464C3.98725 1.07361 5.37207 0.5 6.81603 0.5C8.25998 0.5 9.6448 1.07361 10.6658 2.09464C11.6869 3.11567 12.2605 4.50049 12.2605 5.94444Z" stroke="white" stroke-linecap="round"/>
                    </svg>
                </a>
                <a href=""  class="mobile-cart" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight">
                  <svg width="17" height="15" viewBox="0 0 17 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M5.3916 7.08824V3.79412C5.3916 2.92046 5.73866 2.08259 6.35643 1.46482C6.97419 0.847058 7.81207 0.5 8.68572 0.5C9.55937 0.5 10.3972 0.847058 11.015 1.46482C11.6328 2.08259 11.9798 2.92046 11.9798 3.79412V7.08824" stroke="white" stroke-linecap="round"/>
                    <path d="M1.84549 7.63838C1.96491 6.20462 2.02502 5.48815 2.49773 5.0525C2.97044 4.61768 3.6902 4.61768 5.12891 4.61768H12.2434C13.6813 4.61768 14.401 4.61768 14.8737 5.0525C15.3464 5.48732 15.4066 6.20462 15.526 7.63838L15.9493 12.7163C16.0184 13.5505 16.053 13.968 15.8093 14.234C15.5638 14.5 15.1455 14.5 14.3071 14.5H3.06432C2.22679 14.5 1.80761 14.5 1.56302 14.234C1.31844 13.968 1.35302 13.5505 1.42302 12.7163L1.84549 7.63838Z" stroke="white"/>
                    </svg>
                </a>
              </div>
              <div class="mobile-menu-trigger">
                <!-- <span></span> -->
                <svg width="15" height="9" viewBox="0 0 15 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M0 0.5H15" stroke="white"/>
                  <path d="M0 8.5H15" stroke="white"/>
                  </svg>

              </div>
            </div>
          </div>
        </div>
      </div>
    </nav>
    <!-- mobile searchbar -->
    <div class="mobile-search">
      <div class="close-search">
        X
      </div>
      <div class="mobile-search-bar mt-5">
        <svg stroke="white" class="search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
          <path d="M21.71 20.29l-4.78-4.78A7.92 7.92 0 0 0 18 10c0-4.41-3.59-8-8-8S2 5.59 2 10s3.59 8 8 8c1.92 0 3.68-.68 5.06-1.81l4.78 4.78c.39.39 1.02.39 1.41 0 .39-.39.39-1.02 0-1.41zM10 16c-3.31 0-6-2.69-6-6s2.69-6 6-6 6 2.69 6 6-2.69 6-6 6z"/>

      </svg>
      <input type="search" placeholder="Search">
      </div>
      <div class="mobile-search-quicke-link">
        <p>Quick Link</p>
        <a href="#">Find a store</a>
      </div>
    </div>
    <!-- main slider action -->
    <section class="hero-slider">
      <div class="container">
        <div class="owl-carousel owl-theme" id="hero-carousel">
          <div class="item"><img src="../../../../../public/images/img/headphone.png" alt="Image 1"></div>
          <div class="item"><img src="../../../../../public/images/img/shoe.png" alt="Image 2"></div>
          <div class="item"><img src="../../../../../public/images/img/clock.png" alt="Image 3"></div>
      </div>
      </div>
    </section>
   <!-- best categories -->
   <section class="best-categories">
      <div class="container">
        <h2 class="section-heading text-center text-light mb-md-5 mb-3">Best Categories</h2>
        <div class="categories-list d-flex align-items-center justify-content-md-between">
          <a href="#" class="items d-flex flex-column align-items-center">
            <div class="item-images">
              <img src="../../../../../public/images/img/cat1.png" alt="categores">
            </div>
            <p class="mb-0 mt-3 text-center">Phone</p>
          </a>
          <a href="#" class="items d-flex flex-column align-items-center">
            <div class="item-images">
              <img src="../../../../../public/images/img/cat2.png" alt="categores">
            </div>
            <p class="mb-0 mt-3 text-center">Head Phone</p>
          </a>
          <a href="#" class="items d-flex flex-column align-items-center">
            <div class="item-images">
              <img src="../../../../../public/images/img/cat1.png" alt="categores">
            </div>
            <p class="mb-0 mt-3 text-center">Phone</p>
          </a>
          <a href="#" class="items d-flex flex-column align-items-center">
            <div class="item-images">
              <img src="../../../../../public/images/img/cat1.png" alt="categores">
            </div>
            <p class="mb-0 mt-3 text-center">Phone</p>
          </a>
          <a href="#" class="items d-flex flex-column align-items-center">
            <div class="item-images">
              <img src="../../../../../public/images/img/cat1.png" alt="categores">
            </div>
            <p class="mb-0 mt-3 text-center">Phone</p>
          </a>
          <a href="#" class="items d-flex flex-column align-items-center">
            <div class="item-images">
              <img src="../../../../../public/images/img/cat1.png" alt="categores">
            </div>
            <p class="mb-0 mt-3 text-center">Phone</p>
          </a>
        </div>



      </div>
    </section>


        <!-- must have dolbear -->
    <section class="must-dolbear">
      <div class="container">
        <h2 class="section-heading text-center mb-3 mb-md-5">Must Have Dolbear Selections</h2>
        <div class="categori-button-list d-flex align-items-center justify-content-center">
          <a href="#" class="btn-item active">New Arrived</a>
          <a href="#" class="btn-item">Best Seller</a>
          <a href="#" class="btn-item">Bundle Deals</a>
        </div>
        <!-- slide items -->
        <div class="categori-slide-item">
          <div class="owl-carousel owl-theme must-dolbear-carousel" id="dollbear-carousel">
            <a href="#" class="item">
              <span class="badge">New</span>
             <div class="text-center item-img"><img src="../../../../../public/images/img/mobile1.png" alt="Image 1" class="img-fluid"></div>
              <p class="name">Explore high-quality accessories for Apple devices.</p>
              <p class="price">$109.99</p>
            </a>
            <a href="#" class="item">
              <span class="badge">New</span>
             <div class="text-center item-img"><img src="../../../../../public/images/img/mobile2.png" alt="Image 1" class="img-fluid"></div>
              <p class="name">Explore high-quality accessories for Apple devices.</p>
              <p class="price">$109.99</p>
            </a>
            <a href="#" class="item">
              <span class="badge">New</span>
             <div class="text-center item-img"><img src="../../../../../public/images/img/mobile1.png" alt="Image 1" class="img-fluid"></div>
              <p class="name">Explore high-quality accessories for Apple devices.</p>
              <p class="price">$109.99</p>
            </a>
            <a href="#" class="item">
              <span class="badge">New</span>
             <div class="text-center item-img"><img src="../../../../../public/images/img/mobile1.png" alt="Image 1" class="img-fluid"></div>
              <p class="name">Explore high-quality accessories for Apple devices.</p>
              <p class="price">$109.99</p>
            </a>
            <a href="#" class="item">
              <span class="badge">New</span>
             <div class="text-center item-img"><img src="../../../../../public/images/img/mobile1.png" alt="Image 1" class="img-fluid"></div>
              <p class="name">Explore high-quality accessories for Apple devices.</p>
              <p class="price">$109.99</p>
            </a>

        </div>
        </div>
      </div>
    </section>
    <!-- categori -->
    <section class="categori">
      <div class="container">
        <div class="categories-list d-flex align-items-center justify-content-between">
          <a href="#" class="items d-flex flex-column align-items-center">
            <div class="item-images">
              <img src="../../../../../public/images/img/card.png" alt="categores">
            </div>
            <p class="mb-0 mt-3 text-center">Phone</p>
          </a>
          <a href="#" class="items d-flex flex-column align-items-center">
            <div class="item-images">
              <img src="../../../../../public/images/img/eearphone.png" alt="categores">
            </div>
            <p class="mb-0 mt-3 text-center">Head Phone</p>
          </a>
          <a href="#" class="items d-flex flex-column align-items-center">
            <div class="item-images">
              <img src="../../../../../public/images/img/cat1.png" alt="categores">
            </div>
            <p class="mb-0 mt-3 text-center">Phone</p>
          </a>
          <a href="#" class="items d-flex flex-column align-items-center">
            <div class="item-images">
              <img src="../../../../../public/images/img/cat1.png" alt="categores">
            </div>
            <p class="mb-0 mt-3 text-center">Phone</p>
          </a>
          <a href="#" class="items d-flex flex-column align-items-center">
            <div class="item-images">
              <img src="../../../../../public/images/img/cat1.png" alt="categores">
            </div>
            <p class="mb-0 mt-3 text-center">Phone</p>
          </a>
          <a href="#" class="items d-flex flex-column align-items-center">
            <div class="item-images">
              <img src="../../../../../public/images/img/cat1.png" alt="categores">
            </div>
            <p class="mb-0 mt-3 text-center">Phone</p>
          </a>
        </div>

      </div>
    </section>
    <!-- offer -->
    <section class="offer pb-3 pb-md-5 bg-light-white">
      <div class="container">
        <div class="row">
          <div class="col-6">
            <a href="#" class="d-block">
              <img src="../../../../../public/images/img/offer1.png" alt="offer" class="img-fluid">
            </a>
          </div>
          <div class="col-6">
            <a href="#" class="d-block">
              <img src="../../../../../public/images/img/offer2.png" alt="offer" class="img-fluid">
            </a>
          </div>
        </div>
      </div>
    </section>
    <!-- Featured Product -->
    <section class="featured bg-light-white">
      <div class="container">
        <h2 class="section-heading text-center mb-3 mb-md-5">Featured Product</h2>
        <div class="featured-grid">
          <a href="#" class="d-block featured-item">
            <div class='overflow-hidden'><img src="../../../../../public/images/img/featured1.png" alt="featured images" class="img-fluid text-center"></div>
            <p class="product-name mb-4 mt-3">Explore high-quality accessories for Apple devices. </p>
            <p class="mb-0 product-price">$109.98</p>
          </a>
          <a href="#" class="d-block featured-item">
            <div class='overflow-hidden'><img src="../../../../../public/images/img/featured2.png" alt="featured images" class="img-fluid text-center"></div>
            <p class="product-name mb-4 mt-3">Explore high-quality accessories for Apple devices. </p>
            <p class="mb-0 product-price">$109.98</p>
          </a>
          <a href="#" class="d-block featured-item">
            <div class='overflow-hidden'><img src="../../../../../public/images/img/featured1.png" alt="featured images" class="img-fluid text-center"></div>
            <p class="product-name mb-4 mt-3">Explore high-quality accessories for Apple devices. </p>
            <p class="mb-0 product-price">$109.98</p>
          </a>
          <a href="#" class="d-block featured-item">
            <div class='overflow-hidden'><img src="../../../../../public/images/img/featured1.png" alt="featured images" class="img-fluid text-center"></div>
            <p class="product-name mb-4 mt-3">Explore high-quality accessories for Apple devices. </p>
            <p class="mb-0 product-price">$109.98</p>
          </a>
          <a href="#" class="d-block featured-item">
            <div class='overflow-hidden'><img src="../../../../../public/images/img/featured1.png" alt="featured images" class="img-fluid text-center"></div>
            <p class="product-name mb-4 mt-3">Explore high-quality accessories for Apple devices. </p>
            <p class="mb-0 product-price">$109.98</p>
          </a>
          <a href="#" class="d-block featured-item">
            <div class='overflow-hidden'><img src="../../../../../public/images/img/featured1.png" alt="featured images" class="img-fluid text-center"></div>
            <p class="product-name mb-4 mt-3">Explore high-quality accessories for Apple devices. </p>
            <p class="mb-0 product-price">$109.98</p>
          </a>
        </div>
      </div>
    </section>
    <!-- video sectio -->
    <section class="video-sec">
      <template>
  <video class="d-none d-sm-block" autoplay preload playsinline loop :poster="posterLarge" muted>
    <source :src="videoWebm" type="video/webm">
    <source :src="videoMp4" type="video/mp4">
    <source :src="videoOgg" type="video/ogg">
  </video>
  <video class="d-sm-none" autoplay preload playsinline loop :poster="posterSmall" muted>
    <source :src="videoWebm" type="video/webm">
    <source :src="videoMp4" type="video/mp4">
    <source :src="videoOgg" type="video/ogg">
  </video>
</template>
      <!-- button -->
      <button class="video-btn" data-bs-toggle="modal" data-bs-target="#exampleModal">New Arrivals
        <svg class="arrow ms-3" width="9" height="14" viewBox="0 0 9 14" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M1.82822e-07 0.499999L9 7L-9.53674e-07 13.5L1.82822e-07 0.499999Z" fill="white"/>
        </svg>
        </button>
        <!-- youtube video -->
        <!-- Modal -->
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="white" aria-hidden="true" ><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg></button>
              <div class="modal-body">
                <iframe width="100%" height="100%" src="https://www.youtube.com/embed/mFv0tMZHMfA?enablejsapi=1&origin=https%3A%2F%2Fwww.anker.com&widgetid=6" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
              </div>
            </div>
          </div>
        </div>
    </section>
    <!-- recomonded slider -->
    <section class="recomonded">
      <div class="container">
        <h2 class="section-heading text-center mb-3 mb-md-5">Recomonded By</h2>
        <div class="recomonded-accordion pt-3 pt-md-5">
          <div class="recomonded-accordion-item">
            <div class="item-imgs">
              <img src="https://cdn.shopify.com/s/files/1/0517/6767/3016/files/chargers_celebrity1_768_d25a2123-ec1c-4c91-8c1d-15f0206dffad_3840x.jpg?v=1672300134" alt="images" class="img-fluid">
            </div>
            <div class="item-text active">
              <p>JerryRigEverything</p>
              <p>"It (Anker Nano Pro) will work flawlessly with your iPhone or other devices."</p>
            </div>
          </div>
          <div class="recomonded-accordion-item">
            <div class="item-imgs">
              <img src="https://cdn.shopify.com/s/files/1/0517/6767/3016/files/chargers_celebrity1_768_d25a2123-ec1c-4c91-8c1d-15f0206dffad_3840x.jpg?v=1672300134" alt="images" class="img-fluid">
            </div>
            <div class="item-text">
              <p>JerryRigEverything</p>
              <p>"It (Anker Nano Pro) will work flawlessly with your iPhone or other devices."</p>
            </div>
          </div>
          <div class="recomonded-accordion-item">
            <div class="item-imgs">
              <img src="https://cdn.shopify.com/s/files/1/0517/6767/3016/files/chargers_celebrity1_768_d25a2123-ec1c-4c91-8c1d-15f0206dffad_3840x.jpg?v=1672300134" alt="images" class="img-fluid">
            </div>
            <div class="item-text">
              <p>JerryRigEverything</p>
              <p>"It (Anker Nano Pro) will work flawlessly with your iPhone or other devices."</p>
            </div>
          </div>
          <div class="recomonded-accordion-item">
            <div class="item-imgs">
              <img src="https://cdn.shopify.com/s/files/1/0517/6767/3016/files/chargers_celebrity1_768_d25a2123-ec1c-4c91-8c1d-15f0206dffad_3840x.jpg?v=1672300134" alt="images" class="img-fluid">
            </div>
            <div class="item-text">
              <p>JerryRigEverything</p>
              <p>"It (Anker Nano Pro) will work flawlessly with your iPhone or other devices."</p>
            </div>
          </div>
          <div class="recomonded-accordion-item">
            <div class="item-imgs">
              <img src="https://cdn.shopify.com/s/files/1/0517/6767/3016/files/chargers_celebrity1_768_d25a2123-ec1c-4c91-8c1d-15f0206dffad_3840x.jpg?v=1672300134" alt="images" class="img-fluid">
            </div>
            <div class="item-text">
              <p>JerryRigEverything</p>
              <p>"It (Anker Nano Pro) will work flawlessly with your iPhone or other devices."</p>
            </div>
          </div>
        </div>

      </div>
    </section>
    <!-- get back 15% off -->
    <section class="first-offer bg-black">
      <div class="container">
        <div class="row justify-content-center align-items-center">
          <div class="col-lg-4 mb-3 mb-lg-0">
            <h1>
              <span class="d-block text-1">Get Back</span>
              15% OFF
              <span class="text-2">On Your First Order</span>
            </h1>
          </div>
          <div class="col-lg-1"></div>
          <div class="col-lg-4">
            <div class="news-latter-group">
              <input type="email" class="form-control w-100 mb-3" id="InputEmail" aria-describedby="emailHelp" placeholder="your email">
              <button class="w-100 send-btn">Send</button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- footer -->

    </div>

</template>

<script>
export default {
  data() {
    return {
      posterLarge: 'https://cdn.shopify.com/s/files/1/0650/3769/7280/files/charge-brand-video.jpg?v=1671519405',
      posterSmall: 'https://cdn.shopify.com/s/files/1/0650/3769/7280/files/charge_brand_video_0-767.jpg?v=1671519405',
      videoWebm: 'https://res.cloudinary.com/anker-dtc/video/upload/c_scale,w_1920/v1/anker/home-charger/charge-pc.webm?_a=ATRSRAA0',
      videoMp4: 'https://res.cloudinary.com/anker-dtc/video/upload/c_scale,w_1920/v1/anker/home-charger/charge-pc.mp4?_a=ATRSRAA0',
      videoOgg: 'https://res.cloudinary.com/anker-dtc/video/upload/c_scale,w_1920/v1/anker/home-charger/charge-pc.ogv?_a=ATRSRAA0',
    };
  },
};
</script>

<style scoped>
button.input-group-text.search-input-btn {
    height: 40px !important;
}
</style>



