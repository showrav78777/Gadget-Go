<?php

class OrderTransaction {

    public function getRecordQuery($tran_id)
    {
        $sql = "select * from orders WHERE tran_id='" . $tran_id . "'";
        return $sql;
    }

    public function saveTransactionQuery($post_data)
    {
        $name = $post_data['cus_name'];
        $email = $post_data['cus_email'];
        $phone = $post_data['cus_phone'];
        $amount = $post_data['total_amount'];
        $address = $post_data['cus_add1'];
        $transaction_id = $post_data['tran_id'];
        $currency = $post_data['currency'];

        // Use the new table with simpler column names
        $sql = "INSERT INTO orders (tran_id, amount, currency, status, name, email, phone, address) 
                VALUES ('$transaction_id', '$amount', '$currency', 'Pending', '$name', '$email', '$phone', '$address')";

        return $sql;
    }

    public function updateTransactionQuery($tran_id, $type = 'Success')
    {
        $sql = "UPDATE orders SET status='$type' WHERE tran_id='$tran_id'";

        return $sql;
    }
}

