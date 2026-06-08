<div class="header {{ $headerWhite ?? '' }}">
    <nav class="navbar-custom">
        <div class="container-fluid">
            <div class="menu-bar d-flex align-items-center justify-content-between w-100">
                <div class="menu-icon">
                    <svg width="42" height="33" viewBox="0 0 48 33" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_561_32488)">
                            <rect x="23" width="6" height="23" rx="3" transform="rotate(90 23 0)"
                                fill="white">
                            </rect>
                            <rect x="38" y="13.6154" width="6" height="38" rx="3"
                                transform="rotate(90 38 13.6154)" fill="white">
                            </rect>
                            <rect x="48" y="27.2307" width="6" height="48" rx="3"
                                transform="rotate(90 48 27.2307)" fill="white">
                            </rect>
                        </g>
                        <defs>
                            <clipPath id="clip0_561_32488">
                                <rect width="48" height="33" fill="white"></rect>
                            </clipPath>
                        </defs>
                    </svg>
                </div>
                <a href="tel:02838362369" class="menu-hot-phone d-flex align-items-center gap-1" title=" Gọi Hotline">
                    <svg width="32" height="32" viewBox="0 0 32 32" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M20.6465 18.1082C20.2743 17.736 19.6653 17.736 19.327 18.1082L17.1955 20.2059C16.2144 21.187 14.0152 20.5104 12.2559 18.751C10.4966 16.9917 9.81991 14.7587 10.8011 13.8114L12.8987 11.7137C13.2709 11.3416 13.2709 10.7326 12.8987 10.3943L8.06059 5.52229C7.68843 5.15012 7.07943 5.15012 6.7411 5.52229L5.32011 6.94328C2.47813 9.75142 4.37278 16.2474 9.58308 21.4238C14.7595 26.6003 21.2555 28.5288 24.0975 25.6868L25.5185 24.2658C25.8906 23.8937 25.8906 23.2847 25.5185 22.9463L20.6465 18.1082Z"
                            stroke="white" stroke-width="1.5" stroke-miterlimit="1" stroke-linecap="round"
                            stroke-linejoin="round" />
                        <path d="M15.4365 4C21.831 4 27.0074 9.17646 27.0074 15.5709" stroke="white" stroke-width="1.5"
                            stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M15.4365 7.82288C19.6995 7.82288 23.1843 11.3077 23.1843 15.5707" stroke="white"
                            stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span class="text-white font-hanzel fs-18">02838 362 369</span>
                </a>
                <div class="logo">
                    <a href="{{ route('home') }}">
                        <img src="{{ asset('langding_nano/imgs/logonano.png') }}" alt="Logo ">
                    </a>
                </div>
                <div class="d-flex align-items-center gap-3 menu-hot-link">
                    <div class="d-flex align-items-center gap-2" x-data="{ openMenu: false }">
                        <div class="menu-hot-link-group d-flex align-items-center gap-2 gap-xl-3"
                            :class="{ 'menu-hidden': !openMenu }" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 transform scale-95"
                            x-transition:enter-end="opacity-100 transform scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 transform scale-100"
                            x-transition:leave-end="opacity-0 transform scale-95">
                            <!-- <a href="#" class="link-icon">
                                <svg width="32" height="32" viewBox="0 0 32 32" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_2645_1654)">
                                        <path
                                            d="M18.2892 4C16.5382 4 14.9076 4.44177 13.3976 5.3253C11.9357 6.17671 10.7711 7.33333 9.90361 8.79518C9.02008 10.3052 8.57831 11.9438 8.57831 13.7108C8.57831 14.8514 8.77108 15.9518 9.15663 17.012C9.5261 18.0241 10.0562 18.9558 10.747 19.8072L4.31325 26.241C4.10442 26.4337 4 26.6747 4 26.9639C4 27.253 4.10442 27.498 4.31325 27.6988C4.52209 27.8996 4.76707 28 5.04819 28C5.32932 28 5.5743 27.8956 5.78313 27.6867L12.1928 21.2771C13.0442 21.9679 13.988 22.498 15.0241 22.8675C16.0602 23.2369 17.1486 23.4217 18.2892 23.4217C20.0562 23.4217 21.6948 22.9799 23.2048 22.0964C24.6667 21.245 25.8233 20.0884 26.6747 18.6265C27.5582 17.1165 28 15.4779 28 13.7108C28 11.9438 27.5582 10.3052 26.6747 8.79518C25.8233 7.33333 24.6667 6.17671 23.2048 5.3253C21.6948 4.44177 20.0562 4 18.2892 4ZM18.2892 21.9277C16.8112 21.9277 15.4297 21.5502 14.1446 20.7952C12.9076 20.0723 11.9277 19.0924 11.2048 17.8554C10.4498 16.5703 10.0723 15.1888 10.0723 13.7108C10.0723 12.2329 10.4498 10.8514 11.2048 9.56627C11.9277 8.32932 12.9076 7.3494 14.1446 6.62651C15.4297 5.87149 16.8112 5.49398 18.2892 5.49398C19.7671 5.49398 21.1486 5.87149 22.4337 6.62651C23.6707 7.3494 24.6506 8.32932 25.3735 9.56627C26.1285 10.8514 26.506 12.2329 26.506 13.7108C26.506 15.1888 26.1285 16.5703 25.3735 17.8554C24.6506 19.0924 23.6707 20.0723 22.4337 20.7952C21.1486 21.5502 19.7671 21.9277 18.2892 21.9277Z"
                                            fill="white" />
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_2645_1654">
                                            <rect width="32" height="32" fill="white" />
                                        </clipPath>
                                    </defs>
                                </svg>
                            </a> -->
                            @if (!Auth()->user() || Auth()->user()->role !== 'dealer')
                            <a href="{{ route('cart') }}" class="link-icon icon-cart">
                                <svg width="32" height="32" viewBox="0 0 32 32" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_2645_1659)">
                                        <path
                                            d="M11.4166 27.9167C12.4291 27.9167 13.2499 27.0959 13.2499 26.0833C13.2499 25.0708 12.4291 24.25 11.4166 24.25C10.4041 24.25 9.58325 25.0708 9.58325 26.0833C9.58325 27.0959 10.4041 27.9167 11.4166 27.9167Z"
                                            fill="white" />
                                        <path
                                            d="M23.3333 27.9167C24.3459 27.9167 25.1667 27.0959 25.1667 26.0833C25.1667 25.0708 24.3459 24.25 23.3333 24.25C22.3208 24.25 21.5 25.0708 21.5 26.0833C21.5 27.0959 22.3208 27.9167 23.3333 27.9167Z"
                                            fill="white" />
                                        <path
                                            d="M3.16675 5H5.91675L10.1277 20.1571C10.2349 20.5431 10.4655 20.8834 10.7844 21.1259C11.1033 21.3684 11.4928 21.4998 11.8934 21.5H23.2188C23.6196 21.5001 24.0094 21.3688 24.3286 21.1263C24.6477 20.8837 24.8785 20.5433 24.9857 20.1571L27.9167 9.58333H7.18977"
                                            stroke="white" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_2645_1659">
                                            <rect width="32" height="32" fill="white" />
                                        </clipPath>
                                    </defs>
                                </svg>
                                <span class="cart-badge">{{ $cartCount > 99 ? '99+' : $cartCount }}</span>
                            </a>
                            @endif
                            <!-- <a href="#" class="link-icon">
                                <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_2645_1669)">
                                        <path
                                            d="M16.0001 28C15.6204 28 15.2545 27.8609 14.9692 27.6081C13.8919 26.6552 12.8533 25.7597 11.937 24.9698L11.9323 24.9657C9.24571 22.6497 6.92576 20.6497 5.31159 18.6794C3.50722 16.4769 2.66675 14.3885 2.66675 12.1071C2.66675 9.89052 3.4181 7.84556 4.78222 6.3487C6.16263 4.83415 8.05675 4 10.1163 4C11.6556 4 13.0653 4.49231 14.3062 5.46313C14.9324 5.95313 15.5 6.55287 16.0001 7.2524C16.5003 6.55287 17.0678 5.95313 17.6942 5.46313C18.9351 4.49231 20.3448 4 21.8841 4C23.9434 4 25.8377 4.83415 27.2182 6.3487C28.5823 7.84556 29.3334 9.89052 29.3334 12.1071C29.3334 14.3884 28.4932 16.4769 26.6888 18.6792C25.0746 20.6497 22.7548 22.6495 20.0687 24.9653C19.1507 25.7565 18.1105 26.6534 17.0308 27.6085C16.7454 27.861 16.3792 28.0001 16.0001 28ZM10.1163 5.5802C8.49826 5.5802 7.01185 6.23348 5.9305 7.41973C4.8331 8.62396 4.22862 10.2886 4.22862 12.1071C4.22862 14.0259 4.93357 15.7419 6.5142 17.6712C8.0419 19.536 10.3142 21.4949 12.9453 23.7632L12.9501 23.7673C13.8699 24.5602 14.9126 25.4593 15.9978 26.4192C17.0896 25.4574 18.1339 24.557 19.0555 23.7628C21.6863 21.4945 23.9585 19.536 25.4862 17.6712C27.0666 15.7419 27.7715 14.0259 27.7715 12.1071C27.7715 10.2885 27.1671 8.62391 26.0697 7.41973C24.9885 6.23348 23.502 5.5802 21.8841 5.5802C20.6988 5.5802 19.6105 5.9614 18.6496 6.71299C17.7933 7.38312 17.1968 8.23023 16.847 8.82296C16.6672 9.12776 16.3507 9.30969 16.0001 9.30969C15.6495 9.30969 15.3329 9.12776 15.1531 8.82296C14.8036 8.23023 14.2071 7.38312 13.3505 6.71299C12.3897 5.9614 11.3014 5.5802 10.1163 5.5802Z"
                                            fill="white" />
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_2645_1669">
                                            <rect width="32" height="32" fill="white" />
                                        </clipPath>
                                    </defs>
                                </svg>
                            </a> -->
                            <a href="{{ route('shop') }}" class="link-icon">
                                <svg width="32" height="32" viewBox="0 0 32 32" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_2645_1676)">
                                        <path
                                            d="M8.42099 28H23.5787C23.9934 28 24.404 27.9183 24.7871 27.7596C25.1703 27.6009 25.5184 27.3683 25.8116 27.0751C26.1049 26.7818 26.3375 26.4337 26.4962 26.0506C26.6549 25.6675 26.7365 25.2568 26.7365 24.8421V15.0274C27.133 14.6728 27.4503 14.2388 27.668 13.7535C27.8856 13.2682 27.9986 12.7425 27.9997 12.2106V11.579C28.002 11.5123 27.9913 11.4458 27.9681 11.3832L26.0734 6.09694C25.853 5.47916 25.4458 4.94532 24.9082 4.56956C24.3706 4.19381 23.7293 3.99477 23.0734 4.0001H8.92625C8.27802 3.99991 7.64544 4.19921 7.11439 4.57095C6.58335 4.94269 6.17956 5.46885 5.95787 6.07799L4.06316 11.3643C4.02651 11.4303 4.00494 11.5037 4 11.579V12.2106C4.00108 12.7425 4.1141 13.2682 4.33173 13.7535C4.54936 14.2388 4.86671 14.6728 5.26314 15.0274V24.8421C5.26314 25.6796 5.59584 26.4829 6.18806 27.0751C6.78027 27.6673 7.58348 28 8.42099 28ZM24.2103 14.7369H22.9471C22.2771 14.7369 21.6345 14.4707 21.1608 13.997C20.687 13.5232 20.4208 12.8806 20.4208 12.2106H26.7365C26.7365 12.8806 26.4704 13.5232 25.9966 13.997C25.5228 14.4707 24.8803 14.7369 24.2103 14.7369ZM23.0734 5.26326C23.4622 5.26695 23.8404 5.39013 24.1567 5.6161C24.4731 5.84206 24.7123 6.15987 24.8418 6.52641L26.4523 10.9474H20.3135L19.284 5.26326H23.0734ZM13.9978 5.26326H18.0019L19.0314 10.9474H12.9683L13.9978 5.26326ZM19.1577 12.2106C19.1577 12.8806 18.8915 13.5232 18.4178 13.997C17.944 14.4707 17.3014 14.7369 16.6314 14.7369H15.3683C14.6983 14.7369 14.0557 14.4707 13.5819 13.997C13.1082 13.5232 12.842 12.8806 12.842 12.2106H19.1577ZM15.3683 16.0001H16.6314C17.2571 15.9996 17.873 15.8442 18.424 15.5477C18.975 15.2513 19.4441 14.823 19.7893 14.3011C20.1345 14.823 20.6035 15.2513 21.1545 15.5477C21.7055 15.8442 22.3214 15.9996 22.9471 16.0001H24.2103C24.6408 15.9987 25.068 15.9239 25.4734 15.779V24.8421C25.4734 25.3446 25.2738 25.8266 24.9185 26.1819C24.5631 26.5372 24.0812 26.7368 23.5787 26.7368H20.4208C15.2196 26.7368 15.9999 26.7368 11.5788 26.7368H8.42099C7.91849 26.7368 7.43656 26.5372 7.08123 26.1819C6.7259 25.8266 6.52628 25.3446 6.52628 24.8421V15.779C6.93171 15.9239 7.35888 15.9987 7.78942 16.0001H9.05257C9.67828 15.9996 10.2941 15.8442 10.8452 15.5477C11.3962 15.2513 11.8652 14.823 12.2104 14.3011C12.5556 14.823 13.0246 15.2513 13.5757 15.5477C14.1267 15.8442 14.7426 15.9996 15.3683 16.0001ZM7.15785 6.52641C7.28742 6.15987 7.52661 5.84206 7.84296 5.6161C8.15931 5.39013 8.53751 5.26695 8.92625 5.26326H12.7157L11.6862 10.9474H5.53472L7.15785 6.52641ZM11.5788 12.2106C11.5788 12.8806 11.3127 13.5232 10.8389 13.997C10.3651 14.4707 9.72258 14.7369 9.05257 14.7369H7.78942C7.11941 14.7369 6.47684 14.4707 6.00307 13.997C5.5293 13.5232 5.26314 12.8806 5.26314 12.2106H11.5788Z"
                                            fill="white" />
                                        <path
                                            d="M9.53198 18.8418C9.93042 18.8418 11.0258 18.9053 11.0261 19.3994C11.0261 19.5708 10.8906 19.9199 10.5603 19.9199C10.2902 19.9198 10.1473 19.6788 9.53198 19.6787C8.99881 19.6787 8.78101 19.8629 8.78101 20.0596C8.78193 20.6932 11.1609 20.5732 11.1609 22.1709C11.1609 23.0841 10.3727 23.5791 9.28394 23.5791C8.3003 23.5791 7.47437 23.1724 7.47437 22.7539C7.47459 22.5383 7.70006 22.2217 7.98511 22.2217C8.33796 22.2219 8.56339 22.6904 9.26147 22.6904C9.60699 22.6904 9.98991 22.5768 9.98999 22.2725C9.98999 21.5051 7.60913 21.6382 7.60913 20.1543C7.60919 19.2032 8.59352 18.8418 9.53198 18.8418ZM18.1667 18.8799C19.2028 18.8799 20.0143 19.2856 20.0144 20.376V22.0566C20.0144 23.1473 19.2029 23.5527 18.1667 23.5527C17.1307 23.5527 16.3269 23.1474 16.3269 22.0566V20.376C16.3271 19.2855 17.1309 18.8799 18.1667 18.8799ZM14.99 18.8799C15.2829 18.8799 15.5759 18.9685 15.5759 19.1777V23.2109C15.5759 23.4135 15.2829 23.5156 14.99 23.5156C14.6976 23.5155 14.4051 23.4137 14.405 23.2109V21.4922H12.9109V23.2109C12.9108 23.4135 12.6178 23.5156 12.325 23.5156C12.0321 23.5156 11.7391 23.4138 11.739 23.2109V19.1777C11.739 18.9685 12.032 18.8799 12.325 18.8799C12.6178 18.8799 12.9109 18.9685 12.9109 19.1777V20.7314H14.405V19.1777C14.4051 18.9686 14.6976 18.88 14.99 18.8799ZM22.7546 18.8799C23.7457 18.8799 24.527 19.2728 24.5271 20.3379V20.3701C24.5269 21.4353 23.7156 21.8408 22.6794 21.8408H21.9363V23.2109C21.9362 23.4135 21.6432 23.5156 21.3503 23.5156C21.0575 23.5156 20.7645 23.4138 20.7644 23.2109V19.1777C20.7645 19.0193 20.9373 18.8799 21.2 18.8799H22.7546ZM18.1667 19.7422C17.7466 19.7422 17.4979 19.9387 17.4978 20.376V22.0566C17.4978 22.4941 17.7465 22.6904 18.1667 22.6904C18.5872 22.6904 18.8425 22.494 18.8425 22.0566V20.376C18.8424 19.9389 18.5871 19.7423 18.1667 19.7422ZM21.9363 19.7422V21.0801H22.6794C23.0999 21.0801 23.3552 20.8773 23.3552 20.4463V20.376C23.3551 19.945 23.0999 19.7422 22.6794 19.7422H21.9363Z"
                                            fill="white" />
                                        <path
                                            d="M9.53198 18.8418C9.93042 18.8418 11.0258 18.9053 11.0261 19.3994C11.0261 19.5708 10.8906 19.9199 10.5603 19.9199C10.2902 19.9198 10.1473 19.6788 9.53198 19.6787C8.99881 19.6787 8.78101 19.8629 8.78101 20.0596C8.78193 20.6932 11.1609 20.5732 11.1609 22.1709C11.1609 23.0841 10.3727 23.5791 9.28394 23.5791C8.3003 23.5791 7.47437 23.1724 7.47437 22.7539C7.47459 22.5383 7.70006 22.2217 7.98511 22.2217C8.33796 22.2219 8.56339 22.6904 9.26147 22.6904C9.60699 22.6904 9.98991 22.5768 9.98999 22.2725C9.98999 21.5051 7.60913 21.6382 7.60913 20.1543C7.60919 19.2032 8.59352 18.8418 9.53198 18.8418ZM18.1667 18.8799C19.2028 18.8799 20.0143 19.2856 20.0144 20.376V22.0566C20.0144 23.1473 19.2029 23.5527 18.1667 23.5527C17.1307 23.5527 16.3269 23.1474 16.3269 22.0566V20.376C16.3271 19.2855 17.1309 18.8799 18.1667 18.8799ZM14.99 18.8799C15.2829 18.8799 15.5759 18.9685 15.5759 19.1777V23.2109C15.5759 23.4135 15.2829 23.5156 14.99 23.5156C14.6976 23.5155 14.4051 23.4137 14.405 23.2109V21.4922H12.9109V23.2109C12.9108 23.4135 12.6178 23.5156 12.325 23.5156C12.0321 23.5156 11.7391 23.4138 11.739 23.2109V19.1777C11.739 18.9685 12.032 18.8799 12.325 18.8799C12.6178 18.8799 12.9109 18.9685 12.9109 19.1777V20.7314H14.405V19.1777C14.4051 18.9686 14.6976 18.88 14.99 18.8799ZM22.7546 18.8799C23.7457 18.8799 24.527 19.2728 24.5271 20.3379V20.3701C24.5269 21.4353 23.7156 21.8408 22.6794 21.8408H21.9363V23.2109C21.9362 23.4135 21.6432 23.5156 21.3503 23.5156C21.0575 23.5156 20.7645 23.4138 20.7644 23.2109V19.1777C20.7645 19.0193 20.9373 18.8799 21.2 18.8799H22.7546ZM18.1667 19.7422C17.7466 19.7422 17.4979 19.9387 17.4978 20.376V22.0566C17.4978 22.4941 17.7465 22.6904 18.1667 22.6904C18.5872 22.6904 18.8425 22.494 18.8425 22.0566V20.376C18.8424 19.9389 18.5871 19.7423 18.1667 19.7422ZM21.9363 19.7422V21.0801H22.6794C23.0999 21.0801 23.3552 20.8773 23.3552 20.4463V20.376C23.3551 19.945 23.0999 19.7422 22.6794 19.7422H21.9363Z"
                                            stroke="white" />
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_2645_1676">
                                            <rect width="32" height="32" fill="white" />
                                        </clipPath>
                                    </defs>
                                </svg>
                            </a>
                            @if (Auth()->user() && (Auth()->user()->role === 'dealer' || Auth()->user()->role === 'customer'))
                            <a href="{{ Auth()->user()->role === 'dealer' ? route('dealer.dashboard') : route('customer.dashboard') }}"
                                class="link-icon">
                                <svg width="32" height="32" viewBox="0 0 32 32" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_2645_1681)">
                                        <path
                                            d="M27.8565 26.5396C26.0033 23.3834 23.1475 21.1201 19.8148 20.0472C21.4633 19.0804 22.7441 17.6072 23.4605 15.8538C24.1769 14.1004 24.2893 12.1638 23.7803 10.3414C23.2714 8.51906 22.1694 6.91165 20.6434 5.76604C19.1174 4.62044 17.2519 4 15.3334 4C13.4149 4 11.5494 4.62044 10.0234 5.76604C8.49748 6.91165 7.39542 8.51906 6.88649 10.3414C6.37757 12.1638 6.48993 14.1004 7.20632 15.8538C7.92271 17.6072 9.20351 19.0804 10.852 20.0472C7.51929 21.1189 4.66352 23.3822 2.81037 26.5396C2.74241 26.6488 2.69734 26.7703 2.6778 26.8969C2.65827 27.0235 2.66467 27.1527 2.69663 27.2768C2.72859 27.4009 2.78547 27.5175 2.8639 27.6196C2.94233 27.7218 3.04073 27.8074 3.15329 27.8714C3.26585 27.9355 3.39029 27.9767 3.51927 27.9926C3.64826 28.0085 3.77917 27.9988 3.90428 27.964C4.02939 27.9293 4.14617 27.8702 4.24773 27.7903C4.34929 27.7103 4.43357 27.6112 4.4956 27.4986C6.78801 23.5955 10.8399 21.2652 15.3334 21.2652C19.827 21.2652 23.8788 23.5955 26.1712 27.4986C26.2333 27.6112 26.3175 27.7103 26.4191 27.7903C26.5207 27.8702 26.6374 27.9293 26.7626 27.964C26.8877 27.9988 27.0186 28.0085 27.1476 27.9926C27.2765 27.9767 27.401 27.9355 27.5135 27.8714C27.6261 27.8074 27.7245 27.7218 27.8029 27.6196C27.8814 27.5175 27.9382 27.4009 27.9702 27.2768C28.0022 27.1527 28.0086 27.0235 27.989 26.8969C27.9695 26.7703 27.9244 26.6488 27.8565 26.5396ZM8.51948 12.6342C8.51948 11.3065 8.91911 10.0086 9.66783 8.90469C10.4166 7.80075 11.4808 6.94033 12.7258 6.43224C13.9709 5.92416 15.341 5.79122 16.6627 6.05024C17.9845 6.30926 19.1987 6.94861 20.1516 7.88743C21.1045 8.82626 21.7535 10.0224 22.0164 11.3246C22.2793 12.6268 22.1444 13.9765 21.6287 15.2032C21.1129 16.4298 20.2396 17.4782 19.119 18.2158C17.9985 18.9535 16.6811 19.3472 15.3334 19.3472C13.5268 19.3453 11.7948 18.6374 10.5174 17.3789C9.23993 16.1204 8.52141 14.414 8.51948 12.6342Z"
                                            fill="white" />
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_2645_1681">
                                            <rect width="32" height="32" fill="white" />
                                        </clipPath>
                                    </defs>
                                </svg>
                            </a>
                            <a href="{{ route('logout') }}" class="link-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                    fill="white" class="bi bi-box-arrow-right" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd"
                                        d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0z" />
                                    <path fill-rule="evenodd"
                                        d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z" />
                                </svg>
                            </a>
                            @else
                            <a href="{{ route('login') }}" class="link-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                    fill="white" class="bi bi-box-arrow-in-left" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd"
                                        d="M10 3.5a.5.5 0 0 0-.5-.5h-8a.5.5 0 0 0-.5.5v9a.5.5 0 0 0 .5.5h8a.5.5 0 0 0 .5-.5v-2a.5.5 0 0 1 1 0v2A1.5 1.5 0 0 1 9.5 14h-8A1.5 1.5 0 0 1 0 12.5v-9A1.5 1.5 0 0 1 1.5 2h8A1.5 1.5 0 0 1 11 3.5v2a.5.5 0 0 1-1 0z" />
                                    <path fill-rule="evenodd"
                                        d="M4.146 8.354a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H14.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708z" />
                                </svg>
                            </a>
                            @endif
                        </div>
                        <a href="javascript:void(0)" class="link-icon menu-hot-link-open d-none"
                            @click="openMenu = !openMenu">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#ffffff"
                                class="bi bi-grid-fill" viewBox="0 0 16 16">
                                <path
                                    d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5zm8 0A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5zm-8 8A1.5 1.5 0 0 1 2.5 9h3A1.5 1.5 0 0 1 7 10.5v3A1.5 1.5 0 0 1 5.5 15h-3A1.5 1.5 0 0 1 1 13.5zm8 0A1.5 1.5 0 0 1 10.5 9h3a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 9 13.5z" />
                            </svg>
                        </a>
                    </div>
                    @if (!Auth()->user() || Auth()->user()->role !== 'dealer')
                    <!-- Language Switcher -->
                    <div class="language-switcher" x-data="{ open: false }">
                        <button @click="open = !open" class="btn-language">
                            @if (app()->getLocale() === 'en')
                            <img src="{{ asset('langding/imgs/en.svg') }}" alt="English">
                            <span>EN</span>
                            @else
                            <img src="{{ asset('langding/imgs/vi.svg') }}" alt="Vietnamese">
                            <span>VI</span>
                            @endif
                            <svg class="dropdown-icon" :class="{ 'rotate': open }" width="12" height="12"
                                viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6 9L1 4L11 4L6 9Z" fill="currentColor" />
                            </svg>
                        </button>

                        <div x-show="open" @click.away="open = false" class="language-dropdown" x-transition
                            style="display: none;">
                            <a href="{{ url('/language/vi') }}"
                                class="language-option {{ app()->getLocale() === 'vi' ? 'active' : '' }}">
                                <img src="{{ asset('langding/imgs/vi.svg') }}" alt="Vietnamese">
                                <span>Tiếng Việt</span>
                                @if (app()->getLocale() === 'vi')
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M6 12L2 8L3.5 6.5L6 9L12.5 2.5L14 4L6 12Z" fill="#4CAF50" />
                                </svg>
                                @endif
                            </a>

                            <a href="{{ url('/language/en') }}"
                                class="language-option {{ app()->getLocale() === 'en' ? 'active' : '' }}">
                                <img src="{{ asset('langding/imgs/en.svg') }}" alt="English">
                                <span>English</span>
                                @if (app()->getLocale() === 'en')
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M6 12L2 8L3.5 6.5L6 9L12.5 2.5L14 4L6 12Z" fill="#4CAF50" />
                                </svg>
                                @endif
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </nav>
</div>
<div class="nav-menu">
    <div class="menu-top">
        <div class="close-menu">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0_100_9268)">
                    <path
                        d="M19 6.41L17.59 5L12 10.59L6.41 5L5 6.41L10.59 12L5 17.59L6.41 19L12 13.41L17.59 19L19 17.59L13.41 12L19 6.41Z"
                        fill="white" />
                </g>
                <defs>
                    <clipPath id="clip0_100_9268">
                        <rect width="24" height="24" fill="white" />
                    </clipPath>
                </defs>
            </svg>
        </div>
        <div class="search-menu">
            <input type="text" id="searchKeyword" class="form-control" placeholder="{{ __('messages.search_placeholder') }}">
            <div class="input-group-icon" id="searchAll">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_100_9269)">
                        <path
                            d="M15.5 14H14.71L14.43 13.73C15.41 12.59 16 11.11 16 9.5C16 5.91 13.09 3 9.5 3C5.91 3 3 5.91 3 9.5C3 13.09 5.91 16 9.5 16C11.11 16 12.59 15.41 13.73 14.43L14 14.71V15.5L19 20.49L20.49 19L15.5 14ZM9.5 14C7.01 14 5 11.99 5 9.5C5 7.01 7.01 5 9.5 5C11.99 5 14 7.01 14 9.5C14 11.99 11.99 14 9.5 14Z"
                            fill="white" />
                    </g>
                    <defs>
                        <clipPath id="clip0_100_9269">
                            <rect width="24" height="24" fill="white" />
                        </clipPath>
                    </defs>
                </svg>
            </div>
        </div>
    </div>
    <div class="menu-center">
        <ul class="list-unstyled fw-bold">
            <li><a href="{{ route('home') }}" class="text-white fs-16">{{ __('messages.home') }}</a></li>
            <li>
                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('about') }}" class="text-white fs-16">{{ __('messages.' . 'about_' . 'casu' . 'mina') }}</a>
                    <span class="show-level-1"><i class="fas fa-chevron-right text-white"></i></span>
                </div>

                <ul class="menu-level-1 list-unstyled">
                    @php
                    $aboutMenuPosts = $aboutNanocoatingsPosts ?? (${'gioiThieu' . 'Casu' . 'minaPosts'} ?? []);
                    @endphp
                    @if (count($aboutMenuPosts) > 0)
                    @foreach ($aboutMenuPosts as $index => $category)
                    @php
                    $categoryUrl = route('about') . '#media-' . $category['id'] . '-pane';
                    $hasChildren =
                    isset($category['children']) &&
                    is_array($category['children']) &&
                    count($category['children']) > 0;
                    @endphp
                    <li class="menu-item-level-1">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ $categoryUrl }}" class="text-white fs-14 fw-bold">
                                {{ $category['title'] ?? 'N/A' }}
                            </a>
                            @if ($hasChildren)
                            <span class="show-level-2"><i
                                    class="fas fa-chevron-right text-white"></i></span>
                            @endif
                        </div>

                        @if ($hasChildren)
                        <ul class="menu-level-2 list-unstyled">
                            @foreach ($category['children'] as $child)
                            @php
                            $childUrl = route('about') . '#media-' . $child['id'] . '-pane';
                            @endphp
                            <li>
                                <a href="{{ $childUrl }}" class="text-white fs-14 fw-400">
                                    {{ $child['title'] ?? 'N/A' }}
                                </a>
                            </li>
                            @endforeach
                        </ul>
                        @endif
                    </li>
                    @endforeach
                    @else
                    <li class="text-center py-3">
                        <span class="text-white fs-14">{{ __('messages.no_categories') }}</span>
                    </li>
                    @endif
                </ul>
            </li>
            <li>
                <div class="d-flex justify-content-between align-items-center">
                    <a href="javascript:void(0)" class="text-white fs-16">{{ __('messages.product_info') }}</a>
                    <span class="show-level-1"><i class="fas fa-chevron-right text-white"></i></span>
                </div>

                <ul class="menu-level-1 list-unstyled">
                    @if (isset($menuCategories) && count($menuCategories) > 0)
                    @foreach ($menuCategories as $index => $parentCategory)
                    @php
                    $iconNumber = ($index % 6) + 1;
                    $categoryUrl = isset($parentCategory['slug'])
                    ? '/category/' . $parentCategory['slug']
                    : '#';
                    @endphp
                    <li class="menu-item-level-1">
                        <a href="{{ $categoryUrl }}"
                            class="text-white d-flex justify-content-between align-items-center">
                            <span class="fs-14 fw-bold d-flex align-items-center gap-2">
                                <img src="{{ asset('langding/imgs/icon-menu-' . $iconNumber . '.svg') }}"
                                    alt="icon" />
                                <span>{{ $parentCategory['name'] ?? 'N/A' }}</span>
                            </span>
                        </a>

                        @if (isset($parentCategory['children']) && count($parentCategory['children']) > 0)
                        <ul class="menu-level-2 list-unstyled">
                            <li>
                                <a href="{{ $categoryUrl }}/news" class="text-white fs-14 fw-400">
                                    {{ __('messages.new_products') }}
                                </a>
                            </li>
                            @foreach ($parentCategory['children'] as $child)
                            @php
                            $childUrl = isset($child['slug']) ? '/category/' . $child['slug'] : '#';
                            $hasChildren =
                            isset($child['children']) &&
                            is_array($child['children']) &&
                            count($child['children']) > 0;
                            @endphp
                            <li>
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="{{ $childUrl }}" class="text-white fs-14 fw-400">
                                        {{ $child['name'] ?? 'N/A' }}
                                    </a>
                                    @if ($hasChildren)
                                    <span class="show-level-2"><i
                                            class="fas fa-chevron-right text-white"></i></span>
                                    @endif
                                </div>

                                @if ($hasChildren)
                                <ul class="menu-level-2-nested list-unstyled">
                                    @foreach ($child['children'] as $grandchild)
                                    @php
                                    $grandchildUrl = isset($grandchild['slug'])
                                    ? '/category/' . $grandchild['slug']
                                    : '#';
                                    @endphp
                                    <li>
                                        <a href="{{ $grandchildUrl }}"
                                            class="text-white fs-14 fw-400">
                                            {{ $grandchild['name'] ?? 'N/A' }}
                                        </a>
                                    </li>
                                    @endforeach
                                </ul>
                                @endif
                            </li>
                            @endforeach
                        </ul>
                        @endif
                    </li>
                    @endforeach
                    @else
                    <li class="text-center py-3">
                        <span class="text-white fs-14">{{ __('messages.no_categories') }}</span>
                    </li>
                    @endif
                </ul>
            </li>

            <li><a href="{{ route('branch') }}"
                    class="text-white fs-16">{{ __('messages.store_system') }}</a></li>

            <li>
                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('post.category', 'truyen-thong') }}"
                        class="text-white fs-16">{{ __('messages.media') }}</a>
                    <span class="show-level-1"><i class="fas fa-chevron-right text-white"></i></span>
                </div>

                <ul class="menu-level-1 list-unstyled">
                    @if (isset($truyenThongCategories) && count($truyenThongCategories) > 0)
                    @foreach ($truyenThongCategories as $index => $category)
                    @php
                    $categoryUrl = route('post.category', $category['slug']);
                    @endphp
                    <li class="menu-item-level-1">
                        <a href="{{ $categoryUrl }}"
                            class="text-white d-flex justify-content-between align-items-center">
                            <span class="fs-14 fw-bold d-flex align-items-center gap-2">
                                <span>{{ $category['name'] ?? 'N/A' }}</span>
                            </span>
                        </a>
                    </li>
                    @endforeach
                    @else
                    <li class="text-center py-3">
                        <span class="text-white fs-14">{{ __('messages.no_categories') }}</span>
                    </li>
                    @endif
                </ul>
            </li>

            <li>
                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('document') }}"
                        class="text-white fs-16">{{ __('messages.inspection_info') }}</a>
                    <span class="show-level-1"><i class="fas fa-chevron-right text-white"></i></span>
                </div>

                <ul class="menu-level-1 list-unstyled">
                    @if (isset($dangKiemCategories) && count($dangKiemCategories) > 0)
                    @foreach ($dangKiemCategories as $index => $parentCategory)
                    @php
                    $categoryUrl = route('document') . '/' . $parentCategory['slug'];
                    @endphp
                    <li class="menu-item-level-1">
                        <a href="{{ $categoryUrl }}"
                            class="text-white d-flex justify-content-between align-items-center">
                            <span class="fs-14 fw-bold d-flex align-items-center gap-2">
                                <span>{{ $parentCategory['name'] ?? 'N/A' }}</span>
                            </span>
                        </a>
                    </li>
                    @endforeach
                    @else
                    <li class="text-center py-3">
                        <span class="text-white fs-14">{{ __('messages.no_categories') }}</span>
                    </li>
                    @endif
                </ul>
            </li>

            {{-- <li><a href="{{ route('post.category', 'kinh-nghiem-chia-se') }}" class="text-white fs-16">{{ __('messages.experience_sharing') }}</a></li> --}}

            <li>
                <div class="d-flex justify-content-between align-items-center">
                    <a href="javascript:void(0)" class="text-white fs-16">{{ __('messages.support') }}</a>
                    <span class="show-level-1"><i class="fas fa-chevron-right text-white"></i></span>
                </div>

                <ul class="menu-level-1 list-unstyled">
                    <li class="menu-item-level-1">
                        <a href="{{ route('distribution-system') }}"
                            class="text-white d-flex justify-content-between align-items-center">
                            <span class="fs-14 fw-bold d-flex align-items-center gap-2">
                                <span>{{ __('messages.distribution_system') }}</span>
                            </span>
                        </a>
                    </li>
                    <li class="menu-item-level-1">
                        <a href="{{ route('traceability') }}"
                            class="text-white d-flex justify-content-between align-items-center">
                            <span class="fs-14 fw-bold d-flex align-items-center gap-2">
                                <span>{{ __('messages.product_traceability') }}</span>
                            </span>
                        </a>
                    </li>

                    <li class="menu-item-level-1">
                        <a href="{{ route('warranty') }}"
                            class="text-white d-flex justify-content-between align-items-center">
                            <span class="fs-14 fw-bold d-flex align-items-center gap-2">
                                <span>{{ __('messages.warranty_request') }}</span>
                            </span>
                        </a>
                    </li>

                    <li class="menu-item-level-1">
                        <a href="/post/huong-dan-mua-hang"
                            class="text-white d-flex justify-content-between align-items-center">
                            <span class="fs-14 fw-bold d-flex align-items-center gap-2">
                                <span>{{ __('messages.shopping_guide') }}</span>
                            </span>
                        </a>
                    </li>

                    <li class="menu-item-level-1">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="javascript:void(0)"
                                class="text-white d-flex justify-content-between align-items-center">
                                <span class="fs-14 fw-bold d-flex align-items-center gap-2">
                                    <span>{{ __('messages.documents') }}</span>
                                </span>
                            </a>
                            <span class="show-level-2"><i class="fas fa-chevron-right text-white"></i></span>
                        </div>

                        <ul class="menu-level-2-nested list-unstyled">
                            <li>
                                <a href="{{ route('document', 'catalog') }}" class="text-white fs-14 fw-400">
                                    {{ __('messages.catalog') }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('document', 'tai-lieu-khac') }}" class="text-white fs-14 fw-400">
                                    {{ __('messages.other_documents') }}
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li><a href="{{ route('contact') }}" class="text-white fs-16">{{ __('messages.contact') }}</a></li>
        </ul>
    </div>
    <div class="menu-bottom-author fs-14 fw-400 text-white">{{ __('messages.copyright') }}</div>
</div>
@push('scripts')
<script>
    // Hàm xử lý kích hoạt tab theo hash
    function activateTabFromHash() {
        if (window.location.hash) {
            const hash = window.location.hash;
            const tabId = hash.replace('#', '').replace('-pane', '');

            // Tìm button tương ứng
            const triggerEl = document.getElementById(tabId);

            if (triggerEl) {
                // Kích hoạt tab bằng Bootstrap
                const tab = new bootstrap.Tab(triggerEl);
                tab.show();

                // Scroll đến vị trí tab sau khi hiển thị
                setTimeout(() => {
                    const tabsContainer = document.getElementById('box-media-title');
                    if (tabsContainer) {
                        tabsContainer.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }, 300);
            }
        }
    }

    // Chạy khi trang load lần đầu
    document.addEventListener('DOMContentLoaded', function() {
        activateTabFromHash();

        const searchButton = document.getElementById('searchAll');
        const searchInput = document.getElementById('searchKeyword');

        if (!searchButton || !searchInput) return;

        const submitSearch = function() {
            const keyword = (searchInput.value || '').trim();
            if (!keyword) return;

            const url = "{{ route('search-bar') }}" + '?q=' + encodeURIComponent(keyword);
            window.location.href = url;
        };

        searchButton.addEventListener('click', submitSearch);

        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                submitSearch();
            }
        });
    });

    // Chạy khi hash thay đổi
    window.addEventListener('hashchange', activateTabFromHash);
</script>
@endpush