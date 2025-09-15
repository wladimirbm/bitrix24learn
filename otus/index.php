<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<?php
$APPLICATION->SetTitle("Список Домашних работ");
?>
<style>
    .done {
        background-image: url(data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iMzMiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGcgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIj48Y2lyY2xlIGZpbGw9IiMyMDYxREYiIGN4PSIxNiIgY3k9IjE2LjIiIHI9IjE2Ii8+PHBhdGggZD0iTTE0LjU0IDIyLjA2YTEuMiAxLjIgMCAwMS0uOTUtLjQ4bC0zLjA3LTQuMDRhMS4yIDEuMiAwIDExMS45LTEuNDVsMi4wMiAyLjY2IDUuMDctOC4xM2ExLjIgMS4yIDAgMDEyLjAzIDEuMjZsLTUuOTkgOS42MWMtLjIuMzQtLjU2LjU1LS45Ni41NmgtLjA1eiIgZmlsbC1ydWxlPSJub256ZXJvIiBmaWxsPSIjRkZGIi8+PC9nPjwvc3ZnPg==);
        height: 16px;
        width: 28px;
        display: table-cell;
        vertical-align: middle;
        background-position: 50% 50%;
        border-radius: 16px;
        margin-left: 20px;
    }

    .proc {
        background-image: url(data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTYiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBhdGggZD0iTTEzLjU3IDQuMjFhMy43NCAzLjc0IDAgMDAtMi45IDQuMTlDOC44IDYuMiA4Ljg5IDMuNyA4Ljg5LjA5IDIuOTQgMi4zMyA0LjMzIDguOCA0LjE1IDEwLjc3Yy0xLjUtMS4yMi0xLjc4LTQuMTUtMS43OC00LjE1Qy44IDcuNDMgMCA5LjYgMCAxMS4zN2E3LjcxIDcuNzEgMCAxMDE1LjQgMGMwLTIuNTQtMS44NS0zLjctMS44My03LjE2IiBmaWxsPSIjRkY2MDI3Ii8+PC9zdmc+);
        height: 16px;
        width: 28px;
        display: table-cell;
        vertical-align: middle;
        background-position: 50% 50%;
        border-radius: 16px;
        margin-left: 20px;
    }

    h2 {
        display: table;
    }
</style>
<H1><? $APPLICATION->ShowTitle() ?></H1>
<p>Репозиторий: <a href="https://github.com/wladimirbm/bitrix24learn">https://github.com/wladimirbm/bitrix24learn</a></p>
<h2><a href="homework1/">Домашняя работа 1 (Создание и настройка проекта в VScode // ДЗ)</a>
    <div class="done"></div>
</h2>
<h2><a href="homework2/">Домашняя работа 2 (Отладка и логирование // ДЗ)</a>
    <div class="proc"></div>
</h2>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>