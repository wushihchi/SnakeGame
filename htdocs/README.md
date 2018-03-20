# 框架

純框架原始碼

## 分支說明

* master: 框架核心
* dawn: 黎明版本。包含框架與非核心程式，Smarty、資料庫相關、js套件等包含在此分支
* demo: 程式範例

## 檔案目錄架構

    /
    ├─ classes/             類
    │ ├─ controller/        控制器
    │ ├─ core/              核心類
    │ ├─ lib/               自定義類
    │ ├─ model/             模型類
    │ ├─ vendor/            第三方類
    │ └─ view/              視圖類
    │
    ├─ config/              設定文件
    │
    ├─ template/            樣板
    │ └─ error/             錯誤頁樣板
    │
    ├─ www/                 網頁目錄
    │ ├─ assets/            實體物件 (css/js/image)
    │ │
    │ └─ .htaccess
    │ └─ index.php          入口程式
    │
    ├─ bootstrap.php        初始化程式
    └─ process.php          執行程式
