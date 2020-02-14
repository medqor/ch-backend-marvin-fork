<?php

trait shared
{

    /**
     * @return mixed
     */
    protected function checkUsersTable()
    {
        $sql = "SELECT case when count(*) = 0 then 'false' else 'true' end as tableExists FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='user'";
        return ($this->read($this->_db, $sql, [], 'fetch', PDO::FETCH_COLUMN));
    }

    protected function resetUserPassword($email, $password)
    {

        $sql = "UPDATE password_resets set active=0 where lower(email)=lower(:email) ";
        $params = [':email' => $email];
        $this->write($this->_db, $sql, $params);
        $sql = "UPDATE user set password=:password where lower(email)=lower(:email)";
        $params = [':password' => pcrypt($password), ':email' => $email];
        $this->write($this->_db, $sql, $params);
    }

    protected function checkResetToken($token)
    {
        $sql = "SELECT * from password_resets where token=:token and active=1";
        $params = [':token' => $token];
        return $this->read($this->_db, $sql, $params, 'fetch');
    }

    protected function checkResetByEmail($email)
    {
        $sql = "SELECT count(*) from password_resets where lower(email)=lower(:email) and active=1";
        $params = [':email' => $email];
        return $this->read($this->_db, $sql, $params, 'fetch', PDO::FETCH_COLUMN);
    }

    protected function sendResetLink($record, $token)
    {
        $sql = "UPDATE  password_resets set active=0  where lower(email)=lower(:email)";
        $params = [':emai;' => $record['email']];
        $this->write($this->_db, $sql, $params);

        $sql = "INSERT INTO password_resets(created_at, email, token ) VALUES (now(),lower(:email),:token)";
        $params = [':email' => $record['email'], ':token' => $token];
        $this->write($this->_db, $sql, $params);
    }



    /**
     * @param $username
     * @param string $password
     * @return mixed
     */
    protected function getUserByUsernameOrEmail($username, $password = '')
    {

        $sql = "SELECT *, :password as sanitized
                    from users where user_email=:user or user_name = :user  limit 1
                    ";
        $data= $this->query('_app', $sql, [':user' => $username, ':password' => $password],false,false,'fetch');
        return($data);
    }



}
