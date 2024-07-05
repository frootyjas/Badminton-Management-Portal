<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign in first</title>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

    body{
        margin: 0;
        padding: 0;
        font-family: 'Poppins', sans-serif;
    }
    
    h1 {
        text-align: center;
        margin-top: 50px;
    }

    button {
        align-items: center;
        display: flex;
        justify-content: center;
        height: 45px;
        max-width: 150px;
        width: 100%;
        border: none;
        outline: none;
        color: #f1f1f1;
        border-radius: 5px;
        margin: 25px auto;
        background-color: #142850;
        transition: all 0.1s linear;
        cursor: pointer;
        text-transform: uppercase;
    }

    button:hover {
        background-color: #0c7b93 !important;
    }
</style>
</head>
<body>
    <h1>You do not have access to this page.</h1>

    <button onclick="window.location.href='../php/index.php'">Go back</button>
</body>
</html>
