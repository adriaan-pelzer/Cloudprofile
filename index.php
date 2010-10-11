<?php
include "header.php";
?>
        <section id="content">
            <p>
                When you build Twitter Applications, OMNII allows you to create a profile for each user, using any fields you may need. Once you have created and populated a field for a user, the user will only be able to edit it through you. You wil be able to read data created by other services too, though.
            </p>
            <p>
                Submit your email address and an application name below to register an application. Authentication details will then be emailed to the email address you provide.
            </p>
            <form action="index.php" method="post" name="shorten">
                <input type="text" name="email" placeholder="email address" required id="email" />
                <input type="text" name="name" placeholder="service name"  required id="name" />
                <span id="button_container">
                    Register
                    <input name="register" type="submit" value="Register" id="register" />
                </span>
            </form>
        </section>
<?php
include "footer.php";
?>
