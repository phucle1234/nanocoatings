<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',

    // Login page
    'login_title' => 'Sign In',
    'login_subtitle' => 'Welcome to Nanocoatings!',
    'login_placeholder_username' => 'Enter your account',
    'login_placeholder_password' => 'Enter your password',
    'login_remember_me' => 'Remember me',
    'login_forgot_password' => 'Forgot password?',
    'login_button' => 'Sign In',
    'login_no_account' => 'Don\'t have an account?',
    'login_signup_link' => 'Sign up',

    // Register page
    'register_title' => 'Sign Up',
    'register_subtitle' => 'Create a new account to get started',
    'register_placeholder_name' => 'Enter your full name',
    'register_placeholder_email' => 'Enter your email',
    'register_placeholder_phone' => 'Enter your phone number',
    'register_placeholder_address' => 'Enter your address',
    'register_terms_agree' => 'I agree to the',
    'register_terms_service' => 'Terms of Service',
    'register_privacy_policy' => 'Privacy Policy',
    'register_button' => 'Create Account',
    'register_have_account' => 'Already have an account?',
    'register_login_link' => 'Sign in',

    // Forgot password page
    'forgot_password_title' => 'Forgot Password',
    'forgot_password_subtitle' => 'Enter your email to receive a password reset link',
    'forgot_password_placeholder_email' => 'Enter your email',
    'forgot_password_info' => 'We will send a password reset link to this email',
    'forgot_password_button' => 'Send Password Reset Link',
    'forgot_password_back_login' => 'Back to sign in',
    'forgot_password_no_account' => 'Don\'t have an account?',
    'forgot_password_signup_link' => 'Sign up now',

    // Common UI text
    'page_title' => 'Sign In',
    'toast_notification' => 'Notification',
    'toast_error' => 'Error Notification',
    'processing' => 'Processing...',

    // Login validation & error messages
    'login_invalid_credentials' => 'Invalid login credentials.',
    'login_account_inactive' => 'Account is not activated.',
    'account_not_found' => 'Account does not exist.',

    // Login validation attributes
    'attr_username' => 'Username',
    'attr_password' => 'Password',

    // Register validation messages
    'register_success' => 'Registration successful. Please sign in to continue.',
    'register_error' => 'An error occurred during account registration. Please try again later!',
    'register_email_unique' => 'Email has already been used. Please choose another email.',
    'register_phone_unique' => 'Phone number has already been used. Please choose another phone number.',
    'register_phone_required' => 'Phone number is required.',

    // Register validation attributes
    'attr_name' => 'Full Name',
    'attr_email' => 'Email',
    'attr_phone' => 'Phone Number',
    'attr_address' => 'Address',
    'attr_terms' => 'Terms of Service',

    // Forgot password validation messages
    'forgot_password_email_required' => 'Please enter your email.',
    'forgot_password_email_invalid' => 'Email address is invalid.',
    'forgot_password_email_not_found' => 'Email does not exist.',
    'forgot_password_success' => 'Password recovery successful! Please check your email.',
    'forgot_password_error' => 'An error occurred while processing your request. Please try again later.',

    // User management validation attributes
    'attr_code' => 'code',
    'attr_parent_code' => 'parent code',
    'attr_user_name' => 'username',
    'attr_role' => 'role',
    'attr_latitude' => 'latitude',
    'attr_longitude' => 'longitude',
    'attr_city_code' => 'city code',
    'attr_type' => 'type',
    'attr_product_categories' => 'coating product categories',

    // Profile update messages
    'profile_update_success' => 'Profile updated successfully.',
    'password_update_success' => 'Password updated successfully',

    // Middleware access messages
    'please_login' => 'Please sign in.',
    'no_access_dealer' => 'You do not have access to the partner area.',
    'no_access_customer' => 'You do not have access to the customer area.',
    'no_access_area' => 'You do not have access to this area.',
];
