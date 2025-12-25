<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<?php
$APPLICATION->SetTitle("Список Домашних работ");
?>
<style>
    .prog {
        height: 16px;
        width: 16px;
        display: table-cell;
        vertical-align: middle;
        background-position: 50% 50%;
        border-radius: 16px;
        margin-left: 20px;
        background-repeat: no-repeat;
        display: inline-block;
    }

    .done {
        background-image: url(data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iMzMiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGcgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIj48Y2lyY2xlIGZpbGw9IiMyMDYxREYiIGN4PSIxNiIgY3k9IjE2LjIiIHI9IjE2Ii8+PHBhdGggZD0iTTE0LjU0IDIyLjA2YTEuMiAxLjIgMCAwMS0uOTUtLjQ4bC0zLjA3LTQuMDRhMS4yIDEuMiAwIDExMS45LTEuNDVsMi4wMiAyLjY2IDUuMDctOC4xM2ExLjIgMS4yIDAgMDEyLjAzIDEuMjZsLTUuOTkgOS42MWMtLjIuMzQtLjU2LjU1LS45Ni41NmgtLjA1eiIgZmlsbC1ydWxlPSJub256ZXJvIiBmaWxsPSIjRkZGIi8+PC9nPjwvc3ZnPg==);
    }

    .proc {
        background-image: url(data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTYiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBhdGggZD0iTTEzLjU3IDQuMjFhMy43NCAzLjc0IDAgMDAtMi45IDQuMTlDOC44IDYuMiA4Ljg5IDMuNyA4Ljg5LjA5IDIuOTQgMi4zMyA0LjMzIDguOCA0LjE1IDEwLjc3Yy0xLjUtMS4yMi0xLjc4LTQuMTUtMS43OC00LjE1Qy44IDcuNDMgMCA5LjYgMCAxMS4zN2E3LjcxIDcuNzEgMCAxMDE1LjQgMGMwLTIuNTQtMS44NS0zLjctMS44My03LjE2IiBmaWxsPSIjRkY2MDI3Ii8+PC9zdmc+);
    }

    .clock {
        background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMiIgaGVpZ2h0PSIxMiI+PHBhdGggZD0iTTUuOTQ5IDBBNS45NTUgNS45NTUgMCAwMDAgNS45NDlhNS45NTUgNS45NTUgMCAwMDUuOTQ5IDUuOTQ4IDUuOTU0IDUuOTU0IDAgMDA1Ljk0OC01Ljk0OEE1Ljk1NSA1Ljk1NSAwIDAwNS45NDkgMHptMCAxMC42MzFjLTIuNTgyIDAtNC42ODMtMi4xLTQuNjgzLTQuNjgyczIuMTAxLTQuNjgzIDQuNjgzLTQuNjgzYzIuNTgzIDAgNC42ODIgMi4xMDEgNC42ODIgNC42ODNzLTIuMSA0LjY4Mi00LjY4MiA0LjY4MnoiLz48cGF0aCBkPSJNOS4wNDkgNS43NjVINi4zNzVWMi41NTFhLjQ5LjQ5IDAgMTAtLjk4IDB2My43MDRjMCAuMjcxLjIxOS40ODkuNDkuNDg5aDMuMTYzYS40OS40OSAwIDEwLjAwMS0uOTc5eiIvPjwvc3ZnPg==);
    }

    h2 {
        display: block;
        padding: 5px;
        border-bottom: 1px dashed #ccc;
    }
</style>
<H1><? $APPLICATION->ShowTitle() ?></H1>
<p>Репозиторий: <a href="https://github.com/wladimirbm/bitrix24learn">https://github.com/wladimirbm/bitrix24learn</a></p>
<div>
    <h2><a href="homework1/">Домашняя работа 1 (Создание и настройка проекта в VScode // ДЗ)</a>
        <div class="prog done"></div>
    </h2>
</div>
<div>
    <h2><a href="homework2/">Домашняя работа 2 (Отладка и логирование // ДЗ)</a>
        <div class="prog done"></div>
    </h2>
</div>
<div>
    <h2><a href="homework3/">Домашняя работа 3 (Связывание моделей // ДЗ)</a>
        <div class="prog done"></div>
    </h2>
</div>
<div>
    <h2><a href="homework4/">Домашняя работа 4 (Создание своих таблиц БД и написание модели данных к ним // ДЗ)</a>
        <div class="prog done"></div>
    </h2>
</div>
<div>
    <h2><a href="homework5/">Домашняя работа 5 (Компонент списка таблицы БД // ДЗ)</a>
        <div class="prog done"></div>
    </h2>
</div>
<div>
    <h2><a href="homework6/">Домашняя работа 6 (Написание своего модуля // ДЗ)</a>
        <div class="prog done"></div>
    </h2>
</div>
<div>
    <h2><a href="homework7/">Домашняя работа 7 (Создание кастомных полей и встраивание их в систему // ДЗ)</a>
        <div class="prog done"></div>
    </h2>
</div>
<div>
    <h2><a href="homework8/">Домашняя работа 8 (Учимся подключать свои скрипты, взаимодействовать с компонентами из фронтенда // ДЗ)</a>
        <div class="prog done"></div>
    </h2>
</div>
<div>
    <h2><a href="homework9/">Домашняя работа 9 (Написание своих активити для БП // ДЗ)</a>
        <div class="prog done"></div>
    </h2>
</div>
<div>
    <h2><a href="homework10/">Домашняя работа 10 (Обработка событий // ДЗ)</a>
        <div class="prog done"></div>
    </h2>
</div>
<div>
    <h2><a href="homework11/">Домашняя работа 11 (Локальные приложения и вебхуки // ДЗ) - на проверку! </a>
        <div class="prog clock"></div>
    </h2>
</div>
<div>
    <h2><a href="homework11/">Домашняя работа 12 (Добавление собственных методов REST // ДЗ) - в процессе </a>
        <div class="prog proc"></div>
    </h2>
</div>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>