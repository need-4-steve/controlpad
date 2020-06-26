package com.controlpad.pay_fac.report.gateway;

import com.controlpad.payman_common.transaction_batch.TransactionBatch;
import org.apache.ibatis.annotations.Param;
import org.apache.ibatis.annotations.Select;

import java.util.List;

public interface GatewayReportsMapper {

    @Select("<script>" +
            "SELECT * FROM transaction_batches WHERE gateway_connection_id = #{0} AND external_id IN" +
            " <foreach item='item' index='index' collection='list'" +
            " open='(' separator=',' close=')'>" +
            " #{item.externalId}" +
            " </foreach>" +
            "</script>")
    List<TransactionBatch> getTransactionBatchesForGatewayList(long gatewayConnectionId, @Param("list") List<TransactionBatch> transactionBatches);

    @Select("<script>" +
            "SELECT t.id, t.processed, t.payee_user_id, t.payer_user_id, t.gateway_reference_id, tt.slug AS type," +
            " SUM(CASE e.type_id WHEN 3 THEN e.amount ELSE 0 END) AS affiliate," +
            " SUM(CASE e.type_id WHEN 5 THEN e.amount ELSE 0 END) AS consignment," +
            " SUM(CASE e.type_id WHEN 7 THEN e.amount ELSE 0 END) AS fees," +
            " SUM(CASE e.amount IS NOT NULL WHEN 1 THEN e.amount ELSE 0 END) AS eWallet," + // ends up a balance of the records instead of the merchant payment
            " SUM(CASE e.type_id WHEN 9 THEN e.amount ELSE 0 END) AS salesTax" +
            " FROM transactions AS t" +
            " LEFT JOIN entries AS e ON t.id = e.transaction_id" +
            " JOIN transaction_type AS tt ON tt.id = t.transaction_type_id" +
            " WHERE t.gateway_connection_id = #{0} AND t.gateway_reference_id IN" +
            " <foreach item='item' index='index' collection='list'" +
            " open='(' separator=',' close=')'>" +
            " #{item.id}" +
            " </foreach>" +
            " GROUP BY t.id" +
            "</script>")
    List<TransactionBreakdown> calculateTransactionBreakdownsForGatewayList(Long gatewayConnectionId, @Param("list") List<GatewayTransaction> gatewayTransactions);

    @Select("SELECT tb.external_id AS externalId, tb.id AS id," +
            " SUM(CASE e.type_id WHEN 3 THEN e.amount ELSE 0 END) AS affiliate," +
            " SUM(CASE e.type_id WHEN 5 THEN e.amount ELSE 0 END) AS consignment," +
            " SUM(CASE e.type_id WHEN 7 THEN e.amount ELSE 0 END) AS fees," +
            " SUM(CASE e.amount IS NOT NULL WHEN 1 THEN e.amount ELSE 0 END) AS eWallet," + // ends up a balance of the records instead of the merchant payment
            " SUM(CASE e.type_id WHEN 9 THEN e.amount ELSE 0 END) AS salesTax," +
            " SUM(CASE t.processed WHEN 0 THEN t.amount ELSE 0 END) AS notProcessedAmount," +
            " SUM(CASE t.processed WHEN 0 THEN 1 ELSE 0 END) AS notProcessedCount," +
            " SUM(CASE t.processed WHEN 1 THEN t.amount ELSE 0 END) AS processedAmount," +
            " SUM(CASE t.processed WHEN 1 THEN 1 ELSE 0 END) AS processedCount" +
            " FROM transaction_batches AS tb" +
            " JOIN transactions AS t ON tb.id = t.batch_id" +
            " LEFT JOIN entries AS e ON t.id = e.transaction_id" +
            " WHERE tb.gateway_connection_id = #{0} AND tb.external_id = #{1}" +
            " GROUP BY tb.id")
    BatchBreakdown getBatchBreakdown(Long gatewayConnectionId, String externalId);

    @Select("")
    List<GatewayTransaction> fakeGatewayTransactionsForBatch();


}