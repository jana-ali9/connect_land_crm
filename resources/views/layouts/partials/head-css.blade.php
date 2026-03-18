<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="unit-expenses-store" content="{{ route('unit-expenses.store') }}">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    @media print {
        .no-print {
            display: none !important;
        }
    }

    .card1 {
        /* background: #ffffff; */
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        font-family: Arial, sans-serif;
        position: relative;
    }

    .card-content {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        /* 👈 دي اللي خلت الأيقونة تطلع لفوق */
    }

    .text-section .label {
        color: #6b7280;
        margin: 0;
        font-size: 14px;
    }

    .text-section .number {
        font-size: 28px;
        font-weight: bold;
        margin: 5px 0 15px;
        color: #374151;
    }

    .create-btn {
        background-color: #1C75BC;
        color: white;
        border: none;
        padding: 10px 16px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
    }


    .icon-bg {
        background-color: #ecebff;
        padding: 10px;
        border-radius: 12px;
    }

    .icon-bg img {
        width: 60px;
        height: 60px;
    }

    .building-card {
      padding: 10px;
        background-color: #fff;
        border-radius: 20px;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        font-family: Arial, sans-serif;
    }

    .image-container {
        position: relative;
    }

    .image-container img {
        width: 100%;
        height: 250px;
        display: block;
        object-fit: cover;
        border-top-left-radius: 20px;
        border-top-right-radius: 20px;
        border-bottom-left-radius: 20px;
        border-bottom-right-radius: 20px;
    }

    .edit-btn {
        position: absolute;
        bottom: -25px;
        right: 25px;
        background-color: #1C75BC;
        color: white;
        border: none;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        font-size: 20px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        cursor: pointer;
    }

    .card-body {
        padding: 20px;
        padding-top: 30px;
    }

    .card-body h3 {
        margin: 10px 0 10px;
        font-size: 18px;
        font-weight: 600;
    }

    .description {
        color: #9ca3af;
        font-size: 14px;
        margin-bottom: 20px;
    }

    .card-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn1 {
        flex: 1;
        border: none;
        padding: 12px 16px;
        border-radius: 12px;
        font-size: 14px;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        cursor: pointer;
    }

    .btn1.blue {
        background-color: #1C75BC;
        color: #fff;
    }

    .btn1.teal {
        background-color: #23C6C8;
        color: #fff;
    }

    .btn1.red {
        display: flex;
        flex: 1;
        justify-content: center;
        align-items: center;
        padding: 12px 16px;
        margin-bottom: 15px;
        border-radius: 12px;
        font-size: 14px;
        background-color: #ed5565;
        color: #fff;
        text-decoration: none;
        height: 100%;
        /* يضمن نفس ارتفاع الزر */
    }
    .bx {
        font-size: 18px;
    }

</style>
@vite(['resources/scss/app.scss', 'resources/scss/icons.scss'])
@vite(['resources/js/config.js'])
