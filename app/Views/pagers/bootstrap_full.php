<?php if ($pager->getPageCount() > 1) : ?>
    <?php
    $query = $_GET;
    unset($query['page_permintaan']); // hapus page agar tidak dobel
    $queryStr = http_build_query($query);
    $append = $queryStr ? '&' . $queryStr : '';
    ?>

    <nav>
        <ul class="pagination justify-content-center">

            <?php if ($pager->hasPrevious()) : ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $pager->getFirst() . $append ?>" aria-label="First">
                        <span aria-hidden="true">&laquo;&laquo;</span>
                    </a>
                </li>
                <li class="page-item">
                    <a class="page-link" href="<?= $pager->getPrevious() . $append ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
            <?php endif ?>

            <?php foreach ($pager->links() as $link) : ?>
                <li class="page-item <?= $link['active'] ? 'active' : '' ?>">
                    <a class="page-link" href="<?= $link['uri'] . $append ?>">
                        <?= $link['title'] ?>
                    </a>
                </li>
            <?php endforeach ?>

            <?php if ($pager->hasNext()) : ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $pager->getNext() . $append ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
                <li class="page-item">
                    <a class="page-link" href="<?= $pager->getLast() . $append ?>" aria-label="Last">
                        <span aria-hidden="true">&raquo;&raquo;</span>
                    </a>
                </li>
            <?php endif ?>

        </ul>
    </nav>
<?php endif ?>