<!DOCTYPE html>
<html lang="en">
<head>
	<base href="<?= BASE_URL ?>">
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.14.0/css/all.css">
	<link rel="stylesheet" href="css/trongate.css">
	<link rel="stylesheet" href="css/members_area.css">
	<?= $additional_includes_top ?? '' ?>
	<title>SCA Members Area</title>
</head>
<body>
	<header>
		<div class="header-lg container">
			<div class="logo">
				<div>David Connelly's</div>
				<div>Speed Coding Academy</div>
			</div>
			<div>
				<nav>
                    <ul>
                       	<li> <a href="http://localhost/sca/dashboard"><i class="fa fa-tachometer-alt"></i> Dashboard</a></li>
                        <li><a href="http://localhost/sca/curriculum"><i class="fa fa-comments"></i> Forum</a></li>
                       	<li><a href="http://localhost/sca/curriculum"><i class="fa fa-envelope"></i> Messages</a></li>
                       	<li><a href="http://localhost/sca/members-account/display"><i class="fa fa-user-circle"></i> Your Account</a></li>
                        <li><a href="http://localhost/sca/members/logout"><i class="fa fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </nav>
			</div>
		</div>
		<div class="header-sm">
			<div>
				<div id="hamburger">☰</div>
				<div class="logo-sm">Logo</div>
			</div>
			<div>icons</div>
		</div>
	</header>
	<main class="container silver">
		<div class="center-stage">
		    <h1>Headline Ahoy</h1>
		    <h2>Sub Headline Ahoy</h2>

		    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Obcaecati vel accusamus ea ipsa ut fuga quisquam dolor repellat culpa consequuntur commodi aliquam aut tempora hic, velit maiores libero fugiat, possimus?</p>

		    <!-- Example: Unordered list -->
		    <h3>Feature List (unordered)</h3>
		    <ul>
		        <li>Fast loading pages</li>
		        <li>Beautiful default typography</li>
		        <li>Responsive grid with <code>container</code></li>
		        <li>Form elements that look great without extra classes</li>
		        <li>Minimal utility classes when you need tweaks</li>
		    </ul>

		    <!-- Example: Ordered list -->
		    <h3>Quick Start Steps</h3>
		    <ol>
		        <li>Install Trongate</li>
		        <li>Create a new module</li>
		        <li>Write clean HTML</li>
		        <li>Enjoy nice-looking output</li>
		        <li>Profit!</li>
		    </ol>

		    <!-- Example: Buttons – Trongate CSS styles <button> nicely by default -->
		    <h3>Buttons Gallery</h3>
		    <p>
		        <button>Default Button</button>
		        <button class="alt">Alternate Button</button>
		        <button class="danger">Danger Button</button>
		        <button disabled>Disabled</button>
		    </p>
		    <p>
		        <!-- Using <a> styled as button (common pattern) -->
		        <a href="#" class="button">Link as Button</a>
		        <a href="#" class="button alt">Alt Link Button</a>
		        <a href="#" class="button success">Success</a>
		    </p>

		    <!-- Example: Table – Trongate CSS usually gives clean, bordered tables -->
		    <h3>Sample Members Table</h3>
		    <div class="table-wrapper">
			    <table>
			        <thead>
			            <tr>
			                <th>ID</th>
			                <th>Username</th>
			                <th>Email</th>
			                <th>Joined</th>
			                <th>Actions</th>
			            </tr>
			        </thead>
			        <tbody>
			            <tr>
			                <td>101</td>
			                <td>speedcoder42</td>
			                <td>alex@example.com</td>
			                <td>Jan 15, 2025</td>
			                <td>
			                    <button class="alt">Edit</button>
			                    <button class="danger">Delete</button>
			                </td>
			            </tr>
			            <tr>
			                <td>102</td>
			                <td>ninjacoder</td>
			                <td>sara@demo.com</td>
			                <td>Feb 3, 2025</td>
			                <td>
			                    <button class="alt">Edit</button>
			                    <button class="danger">Delete</button>
			                </td>
			            </tr>
			            <tr>
			                <td>103</td>
			                <td>pixelmaster</td>
			                <td>mike@test.io</td>
			                <td>Feb 20, 2025</td>
			                <td>
			                    <button class="alt">Edit</button>
			                    <button class="danger">Delete</button>
			                </td>
			            </tr>
			        </tbody>
			    </table>
			</div>

		    <!-- Example: Card / boxed content area -->
		    <h3>Featured Lesson</h3>
		    <div style="border: 1px solid #ccc; padding: 1.5em; border-radius: 8px; background: #fafafa; margin: 1.5em 0;">
		        <h4>CSS Grid Layout Masterclass</h4>
		        <p>Learn how to build modern layouts without fighting floats ever again.</p>
		        <p><strong>Level:</strong> Intermediate • <strong>Duration:</strong> 45 min</p>
		        <button class="success">Start Lesson →</button>
		    </div>

		    <!-- Blockquote & code example -->
		    <h3>Quick Tip</h3>
		    <blockquote>
		        "Always use <code>box-sizing: border-box;</code> — it will save you hours of debugging!"
		    </blockquote>



		    <hr>

		    <p>More lorem ipsum content below if you want to keep scrolling...</p>
		    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Recusandae quos soluta, ipsam debitis voluptatum iure quidem atque id doloribus dicta...</p>

		</div>
	</main>
	<footer>
		<div class="footer-lg container">
			<a href="#">Logout</a>
        	<a href="#">Get In Touch</a>			
		</div>
		<div class="footer-sm">
			<a href="#">Logout</a>
        	<a href="#">Get In Touch</a>
		</div>
	</footer>
<?= $additional_includes_btm ?? '' ?>
</body>
</html>