# Vault-Pay Project for the Web Challenge

## Description
Vault-Pay is a web application for managing bank accounts. It allows users to check their balances, make deposits, withdrawals, and transfers between accounts.

## Features
- View bank accounts and their balances, with transaction history
- Deposit money into an account
- Withdraw money from an account
- Transfer money between accounts
- Manage users, their accounts, and transactions

## Installation
1. Clone the project repository:
    ```bash
    git clone https://github.com/TonybynMp4/VaultPay
    ```
2. Install PHP dependencies:
    ```bash
    cd VaultPay
    composer install
    ```
3. Configure the database connection in the `.env` file:
    ```dotenv
    DATABASE_URL="postgresql://db_user:db_password@127.0.0.1:5432/VaultPay"
    ```
4. Run the following commands to create the database and tables:
    ```bash
    php bin/console doctrine:database:create
    php bin/console make:migration
    php bin/console doctrine:migrations:migrate
    ```
5. Start the development server:
(Uses the [symfony cli](https://symfony.com/download))
    ```bash
    symfony server:start
    ```

6. Access the application via `http://localhost:8000`

## Usage
- Access the application via `http://localhost:8000`
- Registration: Create a user account by clicking on "Sign Up"
- Login: Log in to your account by clicking on "Log In"
- Dashboard: View an overview of your transactions and perform operations
    - Transaction history: View transactions made account by account
- Client account: View your account information and bank accounts, or create a new account
- Admin page: Manage users, their bank accounts, and associated transactions
    - To add an admin user, create a user and then change the role in the `user` table of the database to "ROLE_ADMIN".
 
## Wireframes
![Accueil](https://github.com/user-attachments/assets/20aeaf97-ef0b-4de2-9012-96407e13286e)
![Tableau de bord](https://github.com/user-attachments/assets/d0fab81e-12c3-48a1-a9d1-f021fa7a6c73)
![Historique](https://github.com/user-attachments/assets/6c4ad535-1331-4f58-81dd-9a3acf4807e5)
![Historique compte](https://github.com/user-attachments/assets/45260bc4-445b-4030-a696-a67e6c058167)
![Compte Client](https://github.com/user-attachments/assets/8f6023b5-09b8-4d31-ba57-f4e83d6fea23)
![Forms](https://github.com/user-attachments/assets/448b8726-c58b-4e8e-99a5-5311159ff11d)
![Panel Admin](https://github.com/user-attachments/assets/d16ba701-e869-40a6-a6aa-ddd818c7881f)

## Databse Schemas


