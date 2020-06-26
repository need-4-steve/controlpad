package com.controlpad.payman_common.payout_job;


import org.apache.ibatis.annotations.Insert;
import org.apache.ibatis.annotations.Options;
import org.apache.ibatis.annotations.Select;
import org.apache.ibatis.annotations.Update;

import java.util.List;

public interface PayoutJobMapper {

    @Select("SELECT * FROM payout_jobs WHERE id = #{0}")
    PayoutJob findById(Long payoutJobId);

    @Select("SELECT * FROM payout_jobs WHERE team_id = #{0} AND status = 'inactive'")
    List<PayoutJob> listInactiveForTeam(String teamId);

    @Select("SELECT id FROM payout_jobs WHERE id = LAST_INSERT_ID()")
    Long getGeneratedId();

    @Select("SELECT * FROM payout_jobs WHERE payment_batch_id = #{0} ORDER BY created_at DESC LIMIT 1")
    PayoutJob findForPaymentBatchId(String paymentBatchId);


    /**
     * Use for when a task doesn't finish due to errors or server restart
     */
    @Update("UPDATE payout_jobs SET status = 'inactive' WHERE id = #{0} AND status <> 'processed'")
    int markInactive(Long payoutJobId);

    @Update("UPDATE payout_jobs SET status = 'queued' WHERE id = #{0} AND status = 'inactive'")
    int markQueued(Long payoutJobId);

    @Update("UPDATE payout_jobs SET status = 'updating' WHERE id = #{0} AND status = 'inactive'")
    int markUpdating(Long payoutJobId);

    @Update("UPDATE payout_jobs SET status = 'processing' WHERE id = #{0} AND status = 'queued'")
    int markProcessing(Long payoutJobId);

    @Update("UPDATE payout_jobs SET status = 'processed' WHERE id = #{0} AND status = 'processing'")
    int markProcessed(Long payoutJobId);

    @Update("UPDATE payout_jobs SET status = 'error' WHERE id = #{0}")
    int markErorr(Long payoutJobId);

    @Update("UPDATE payout_jobs SET status = 'skipped' WHERE id = #{0}")
    int markSkipped(Long payoutJobId);

    @Update("UPDATE payout_jobs SET status = 'inactive' WHERE status = 'queued'")
    int resetQueued();

    @Update("UPDATE payout_jobs SET status = 'inactive' WHERE status = 'processing'")
    int resetProcessing();

    @Insert("INSERT INTO payout_jobs(start_at, status, team_id, payout_scheme, payment_batch_id)" +
            " VALUES(#{startAt}, #{status}, #{teamId}, #{payoutScheme}, #{paymentBatchId})")
    @Options(useGeneratedKeys = true, keyColumn = "id")
    int insert(PayoutJob payoutJob);
}