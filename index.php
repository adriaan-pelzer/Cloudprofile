<?php
include "header.php";
?>
        <section id="content">
            <p>
                When you build Twitter Applications, OMNII allows you to build Twitter applications,
            </p>
            <form action="index.php" method="post" name="shorten">
                <label for="url_in">Email Address</label>
                <input type="text" name="url_in" placeholder="url to be shortened"  required id="text" />
                <label for="url_in">Email Address</label>
                <input type="text" name="url_in" placeholder="url to be shortened"  required id="text" />
                <span id="button_container">
                    Shorten
                    <input name="shorten" type="submit" value="Shorten" id ="button" />
                </span>
            </form>
        </section>
<?php
include "footer.php";
?>
