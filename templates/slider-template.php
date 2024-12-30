<?php
$slider_posts = new WP_Query([
    'post_type' => 'slider',
    'posts_per_page' => 10, // Limit the number of sliders
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC', // Order by most recent post first
]);
$index = 0;

if ($slider_posts->have_posts()) :
    $totalSlides = $slider_posts->post_count; // Get the total number of slides
?>
    <div class="slider-container">
        <div class="slider-wrapper" data-slides="<?= $totalSlides; ?>">
            <div class="slider-track">

            <!-- Only generate clones if there are at least 4 slides -->
            <?php if ($totalSlides > 3) : ?>
                <!-- Clone last set of slides -->
                <?php
                $slider_posts->rewind_posts();
                $last_clones = [];
                while ($slider_posts->have_posts()) : $slider_posts->the_post();
                    $last_clones[] = [
                        'title' => get_the_title(),
                        'image' => get_post_meta(get_the_ID(), '_slider_image_thumbnail', true) ?: get_post_meta(get_the_ID(), '_slider_image', true),
                        'description' => get_post_meta(get_the_ID(), '_slider_description', true),
                    ];
                endwhile;
                $last_clones = array_slice($last_clones, -4); // Take the last 4 slides
                $cloneIndex = $totalSlides - 4;
                foreach ($last_clones as $clone) :
                ?>
                    <div class="slide clone" data-index="<?= $cloneIndex ?>">
                        <div class="slider_image">
                            <?php if (!empty($clone['image'])) : ?>
                                <img src="<?= esc_url($clone['image']); ?>" alt="<?= esc_attr($clone['title']); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="slide-title"><?= esc_html($clone['title']); ?></div>
                        <div class="slide-description"><?= esc_html($clone['description']); ?></div>
                    </div>
                <?php $cloneIndex++; endforeach; ?>
            <?php endif; ?>

            <!-- Original slides -->
            <?php
            $slider_posts->rewind_posts();
            $realIndex = 0; // Start indexing from 0
            while ($slider_posts->have_posts()) : $slider_posts->the_post();
                $slider_image_thumbnail = get_post_meta(get_the_ID(), '_slider_image_thumbnail', true);
                $slider_image_original = get_post_meta(get_the_ID(), '_slider_image', true);
                $slider_image = $slider_image_thumbnail ?: $slider_image_original;
                $slider_description = get_post_meta(get_the_ID(), '_slider_description', true);
            ?>
                <div class="slide" data-index="<?= $realIndex ?>">
                    <div class="slider_image">
                        <?php if ($slider_image) : ?>
                            <img src="<?= esc_url($slider_image); ?>" alt="<?php the_title(); ?>">
                        <?php endif; ?>
                    </div>
                    <div class="slide-title"><?php the_title(); ?></div>
                    <div class="slide-description"><?= esc_html($slider_description); ?></div>
                </div>
            <?php $realIndex++; ?>
            <?php endwhile; ?>

            <!-- Clone first set of slides -->
            <?php if ($totalSlides > 3) : ?>
                <?php
                $slider_posts->rewind_posts();
                $first_clones = [];
                while ($slider_posts->have_posts()) : $slider_posts->the_post();
                    $first_clones[] = [
                        'title' => get_the_title(),
                        'image' => get_post_meta(get_the_ID(), '_slider_image_thumbnail', true) ?: get_post_meta(get_the_ID(), '_slider_image', true),
                        'description' => get_post_meta(get_the_ID(), '_slider_description', true),
                    ];
                endwhile;
                $cloneIndex = $realIndex; // Continue from last real index
                foreach ($first_clones as $clone) :
                ?>
                    <div class="slide clone" data-index="<?= $cloneIndex ?>">
                        <div class="slider_image">
                            <?php if (!empty($clone['image'])) : ?>
                                <img src="<?= esc_url($clone['image']); ?>" alt="<?= esc_attr($clone['title']); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="slide-title"><?= esc_html($clone['title']); ?></div>
                        <div class="slide-description"><?= esc_html($clone['description']); ?></div>
                    </div>
                <?php $cloneIndex++; endforeach; ?>
            <?php endif; ?>

            </div>
        </div>
        <button class="nav-arrow prev" <?= $totalSlides <= 1 ? 'style="display:none;"' : '' ?>></button>
        <button class="nav-arrow next" <?= $totalSlides <= 1 ? 'style="display:none;"' : '' ?>></button>
    </div>
    <div class="pagination"></div>
    <div class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <button class="modal-close">X</button>
            <h2 class="modal-title">Title goes here</h2>
            <p class="modal-description">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
        </div>
    </div>
<?php endif; ?>
<?php wp_reset_postdata(); ?>
