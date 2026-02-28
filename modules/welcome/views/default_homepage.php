<h1 class="mt-2">Enjoy Trongate!</h1>
<h2>The Native PHP Framework</h2>
<p class="mt-2">You have successfully installed Trongate. You're now ready to start building fast, efficient web applications.</p>

<p class="text-center"><?= Modules::run('code_generator/draw_open_code_generator') ?></p>

<div class="mt-3">
    <?php
    echo anchor('https://trongate.io', 'Visit Trongate.io', ['class' => 'button', 'target' => '_blank']);
    echo anchor('https://trongate.io/docs', 'View Documentation', ['class' => 'button alt', 'target' => '_blank']);
    ?>
</div>

<p class="text-center">
    <ul>
        <li><?= anchor('dashboard', 'Dashboard') ?></li>
        <li><?= anchor('video_lessons', 'Video Lessons') ?></li>
        <li><?= anchor('video_lesson', 'View Video Lesson') ?></li>
        <li><?= anchor('your_account', 'Your Account') ?></li>
    </ul>
</p>

<style>
ul {
    width: max-content;
    margin: 3em auto;
}
</style>