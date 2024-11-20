<?php

$loginPage = new Page_Login();
$result = false;
$errorMessage = "Error";
if (isset($_POST["PL_action_ref"])) {

    $firstname = sanitize_text_field($_POST["firstname"]);
    $lastname = sanitize_text_field($_POST["lastname"]);
    $fullname = $firstname." ".$lastname;

    $email = sanitize_text_field($_POST["email"]);
    $password = sanitize_text_field($_POST["password"]);

    if(empty($firstname) || empty($lastname)) {
      $errorMessage = "Firstname or Lastname is required";
    }
    else{
      if(strlen($password) <= 8){
        $errorMessage = "Password must be more than 8 characters";
      }
      else{
        $passwordMD5 = md5($password);
        $code = md5($email);
        $code = substr($code,0,8);
        $result = set_ref_info($fullname,$email,$passwordMD5,$code,"percent",5);
        if(!$result)
          $errorMessage = "Email might already  be in use";
      }
    }
      
}
if(!$result){
?>

<form class="space-y-6" name="login" method="post">
  <div class="rounded-lg border text-card-foreground mx-auto max-w-md bg-white p-6 shadow-lg" data-v0-t="card">
    <div class="flex flex-col space-y-1.5 p-6">
      <h3 class="whitespace-nowrap tracking-tight text-2xl font-bold">Become an Affiliate</h3>
      <p class="text-sm text-gray-500">Join our affiliate program and start earning.</p>
      <?php
        if (isset($_POST["PL_action_ref"]) && !$result)
            alert($errorMessage);
        ?>
    </div>
    <div class="p-6 grid gap-4">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label
            class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-gray-700 mb-3"
            for="firstName"
          >
            First Name
          </label>
          <input
            class="flex h-10 w-full rounded-md bg-background text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[<?= MAIN_COLOR_FOCUS ?>] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 border border-gray-300 px-3 py-2"
            id="firstName"
            name="firstname"
            required
            placeholder="Enter your first name"
          />
        </div>
        <div>
          <label
            class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-gray-700 mb-3"
            for="lastName"
          >
            Last Name
          </label>
          <input
            class="flex h-10 w-full rounded-md bg-background text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[<?= MAIN_COLOR_FOCUS ?>] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 border border-gray-300 px-3 py-2"
            id="lastName"
            name="lastname"
            required
            placeholder="Enter your last name"
          />
        </div>
      </div>
      <div>
        <label
          class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-gray-700 mb-3"
          for="email"
        >
          Email
        </label>
        <input
          class="flex h-10 w-full rounded-md bg-background text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[<?= MAIN_COLOR_FOCUS ?>] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 border border-gray-300 px-3 py-2"
          id="email"
          name="email"
            required
          placeholder="Enter your email"
          type="email"
        />
      </div>
      <div>
        <label
          class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-gray-700 mb-3"
          for="password"
        >
          Password
        </label>
        <input
          class="flex h-10 w-full rounded-md bg-background text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[<?= MAIN_COLOR_FOCUS ?>] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 border border-gray-300 px-3 py-2"
          id="password"
          name="password"
            required
          placeholder="Enter your password"
          type="password"
        />
      </div>
    </div>
    <div class="flex items-center p-6 mt-4">
       <button type="submit" name="PL_action_ref" value="PL" onclick="login_loading()"
                    class="flex w-full justify-center rounded-md bg-[<?= MAIN_COLOR ?>] px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-[<?= MAIN_COLOR_HOVER ?>] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[<?= MAIN_COLOR_FOCUS ?>]">Sign up</button>
    </div>
  </div>
</form>

<?php
} else{
  //post currect
  alert_and_links('Thank you for creating an affiliate account on <strong>'.get_bloginfo('name').'</strong>.',"Account created successfully.",["Home" => network_site_url('/') ,"View Account" => network_site_url('/') . "login-ref" ],'green',"success");

}
?>

