// Compile as ruby-rice? //
#define COMPILE_RUBYRICE
#ifdef COMPILE_RUBYRICE

// Include the ruby-rice classes needed //
#include "rice/Class.hpp"
#include "rice/Constructor.hpp"
#include "rice/String.hpp"
#include "ce.h" // All other commission engine files //
using namespace Rice;

extern "C"

void Init_rice_CE()
{
    Class rb_cCE = define_class<CezEntry>("CeMain")
        .define_constructor(Constructor<CezEntry>()) 
        //.define_method("Startup", &CezEntry::StartupRuby, (Arg("dbname"), Arg("username"), Arg("password"), Arg("hostname")))
        .define_method("Startup", &CezEntry::StartupRuby, (Arg("inifile")))
        .define_method("SetDebugDisplay", &CezEntry::SetDebugDisplay, (Arg("display")))
        .define_method("SetDebugLevel", &CezEntry::SetDebugLevel, (Arg("level")))
        .define_method("InitTables", &CezEntry::InitTables, (Arg("hashpass")))
        .define_method("DropTables", &CezEntry::DropTables)
        .define_method("RebuildLevels", &CezEntry::RebuildLevels, (Arg("system_id")));

//   Class rb_cSystemUser = define_class<CceSystemUser>("CeSystemUser")
//        .define_constructor(Constructor<CceSystemUser>())
//        .define_method("Add", &CceSystemUser::AddRuby, (Arg("email"), Arg("password")))
//        .define_method("Edit", &CceSystemUser::Edit, (Arg("coresysuser_id"), Arg("sysuser_id"), Arg("email"), Arg("password"), Arg("ipaddress") = "127.0.0.1"))
//        .define_method("Query", &CceSystemUser::Query)
//        .define_method("Disable", &CceSystemUser::Disable, (Arg("coresysuser_id"), Arg("sysuser_id")))
//        .define_method("Enable", &CceSystemUser::Enable, (Arg("coresysuser_id"), Arg("sysuser_id")));

    Class rb_cSystem = define_class<CceSystem>("CeSystem")
        .define_constructor(Constructor<CceSystem>())
//        .define_method("Add", &CceSystem::AddRuby, (Arg("systemname"), Arg("payout_type"), Arg("payout_monthday"), Arg("payout_weekday"), Arg("minpay"), Arg("signupbonus"), Arg("psqlimit"), Arg("compression"))) 
//        .define_method("Edit", &CceSystem::EditRuby, (Arg("system_id"), Arg("systemname"), Arg("payout_type"), Arg("payout_monthday"), 
//            Arg("payout_weekday"), Arg("minpay"), Arg("signupbonus"), Arg("psqlimit"), Arg("compression"))) 
            //Arg("updated_url"), Arg("updated_username"), Arg("updated_password")))
//        .define_method("Query", &CceSystem::Query, (Arg("sysuser_id")))
        .define_method("Disable", &CceSystem::DisableRuby, (Arg("system_id")))
        .define_method("Enable", &CceSystem::EnableRuby, (Arg("system_id")))
        .define_method("Get", &CceSystem::GetRuby, (Arg("system_id")));

    Class rb_cUser = define_class<CceUser>("CeUser")  
        .define_constructor(Constructor<CceUser>())
        .define_method("Add", &CceUser::AddRuby, (Arg("system_id"), Arg("user_id"), Arg("parent_id"), Arg("sponsor_id"), Arg("signupdate"), Arg("usertype")))
        .define_method("Edit", &CceUser::EditRuby, (Arg("system_id"), Arg("user_id"), Arg("parent_id"), Arg("sponsor_id"), Arg("signupdate"), Arg("usertype")))
//        .define_method("Query", &CceUser::Query, (Arg("system_id"))) 
        .define_method("Disable", &CceUser::DisableRuby, (Arg("system_id"), Arg("user_id"))) 
        .define_method("Enable", &CceUser::EnableRuby, (Arg("system_id"), Arg("user_id")));

    Class rb_cReceipt = define_class<CceReceipt>("CeReceipt")
        .define_constructor(Constructor<CceReceipt>()) 
        .define_method("Add", &CceReceipt::AddRuby, (Arg("system_id"), Arg("receipt_id"), Arg("user_id"), Arg("wholesale_price"), Arg("retail_price"), Arg("wholesale_date"), Arg("retail_date"), Arg("inv_type"), Arg("commissionable")))
        .define_method("Edit", &CceReceipt::EditRuby, (Arg("system_id"), Arg("receipt_id"), Arg("user_id"), Arg("wholesale_price"), Arg("retail_price"), Arg("wholesale_date"), Arg("retail_date"), Arg("inv_type"), Arg("commissionable")))
//        .define_method("Query", &CceReceipt::Query, (Arg("system_id"), Arg("startdate"), Arg("enddate"))) 
        .define_method("Disable", &CceReceipt::DisableRuby, (Arg("system_id"), Arg("receipt_id")))
        .define_method("Enable", &CceReceipt::EnableRuby, (Arg("system_id"), Arg("receipt_id")));

//    Class rb_cRankRule = define_class<CceRankRule>("CeRankRule")
//        .define_constructor(Constructor<CceRankRule>())
//        .define_method("Add", &CceRankRule::Add, (Arg("system_id"), Arg("rank"), Arg("qualify_type"), Arg("qualify_threshold"), Arg("achvbonus"), 
//            Arg("breakage"), Arg("rulegroup"), Arg("maxdacleg")))
//        .define_method("Edit", &CceRankRule::Edit, (Arg("system_id"), Arg("rank_id"), Arg("rank"), Arg("qualify_type"), Arg("qualify_threshold"), Arg("achvbonus"), 
//            Arg("breakage"), Arg("rulegroup"), Arg("maxdacleg")))
//        .define_method("Query", &CceRankRule::Query, (Arg("system_id"))) 
//        .define_method("Disable", &CceRankRule::Disable, (Arg("system_id"), Arg("rank_id"))) 
//        .define_method("Enable", &CceRankRule::Enable, (Arg("system_id"), Arg("rank_id")));

//    Class rb_cCommRule = define_class<CceCommRule>("CeCommRule")
//        .define_constructor(Constructor<CceCommRule>())
//        .define_method("Add", &CceCommRule::Add, (Arg("system_id"), Arg("rank"), Arg("start_gen"), Arg("end_gen"), Arg("qualify_type"), 
//            Arg("qualify_threshold"), Arg("infinitybonus"), Arg("percent")))
//        .define_method("Edit", &CceCommRule::Edit, (Arg("system_id"), Arg("commrule_id"), Arg("rank"), Arg("start_gen"), Arg("end_gen"), Arg("qualify_type"), 
//            Arg("qualify_threshold"), Arg("infinitybonus"), Arg("percent")))
//        .define_method("Query", &CceCommRule::Query, (Arg("system_id"))) 
//        .define_method("Disable", &CceCommRule::Disable, (Arg("system_id"), Arg("commrule_id"))) 
//        .define_method("Enable", &CceCommRule::Enable, (Arg("system_id"), Arg("commrule_id")));

//    Class rb_cCMCommRule = define_class<CceCMCommRule>("CeCMCommRule")
//        .define_constructor(Constructor<CceCMCommRule>())
//        .define_method("Add", &CceCMCommRule::Add, (Arg("system_id"), Arg("rank"), Arg("start_gen"), Arg("end_gen"), Arg("percent")))
//        .define_method("Edit", &CceCMCommRule::Edit, (Arg("system_id"), Arg("cmcommrule_id"), Arg("rank"), Arg("start_gen"), Arg("end_gen"), Arg("percent")))
//        .define_method("Query", &CceCMCommRule::Query, (Arg("system_id"))) 
//        .define_method("Disable", &CceCMCommRule::Disable, (Arg("system_id"), Arg("cmcommrule_id"))) 
//        .define_method("Enable", &CceCMCommRule::Enable, (Arg("system_id"), Arg("cmcommrule_id")));

//    Class rb_cPoolPot = define_class<CcePoolPot>("CePoolPot")
//        .define_constructor(Constructor<CcePoolPot>())
//        .define_method("Add", &CcePoolPot::Add, (Arg("system_id"), Arg("amount"), Arg("qualifytype"), Arg("startdate"), Arg("enddate")))
//        .define_method("Edit", &CcePoolPot::Edit, (Arg("system_id"), Arg("poolpotid"), Arg("amount"), Arg("qualifytype"), Arg("startdate"), Arg("enddate"))) 
//        .define_method("Query", &CcePoolPot::Query, (Arg("system_id")))
//        .define_method("Disable", &CcePoolPot::Disable, (Arg("system_id"), Arg("poolpotid")))
//        .define_method("Enable", &CcePoolPot::Enable, (Arg("system_id"), Arg("poolpotid")))
//        .define_method("RunPool", &CcePoolPot::RunPool, (Arg("system_id"), Arg("poolpotid")));

//    Class rb_cPoolRule = define_class<CcePoolRule>("CePoolRule")
//        .define_constructor(Constructor<CcePoolRule>())
//        .define_method("Add", &CcePoolRule::Add, (Arg("system_id"), Arg("poolpotid"), Arg("startrank"), Arg("endrank"), Arg("qualifythreshold")))
//        .define_method("Edit", &CcePoolRule::Edit, (Arg("system_id"), Arg("poolruleid"), Arg("startrank"), Arg("endrank"), Arg("qualifythreshold")))
//        .define_method("Query", &CcePoolRule::Query, (Arg("system_id"), Arg("poolpotid")))
//        .define_method("Disable", &CcePoolRule::Disable, (Arg("system_id"), Arg("poolruleid")))
//        .define_method("Enable", &CcePoolRule::Enable, (Arg("system_id"), Arg("poolruleid")));

//    Class rb_cCommission = define_class<CceCommissions>("CeCommission")
//        .define_constructor(Constructor<CceCommissions>())
//        .define_method("Predict", &CceCommissions::Predict, (Arg("system_id"), Arg("startdate"), Arg("enddate")))
//        .define_method("PredictGrandTotal", &CceCommissions::PredictGrandTotal, (Arg("system_id"), Arg("startdate"), Arg("enddate")))
//        .define_method("Calc", &CceCommissions::Calc, (Arg("system_id"), Arg("startdate"), Arg("enddate")))
//        .define_method("QueryBatches", &CceCommissions::QueryBatches, (Arg("system_id")))
//        .define_method("QueryUser", &CceCommissions::QueryUser, (Arg("system_id"), Arg("user_id")))
//        .define_method("QueryComm", &CceCommissions::QueryComm, (Arg("system_id"), Arg("batch_id")))
//        .define_method("FullPredict", &CceCommissions::FullPredict, (Arg("startdate"), Arg("enddate")))
//        .define_method("FullCalc", &CceCommissions::FullCalc, (Arg("startdate"), Arg("enddate")))
//        .define_method("FullCalcSpeed", &CceCommissions::FullCalcSpeed, (Arg("proc_count"), Arg("startdate"), Arg("enddate")))
//        .define_method("SetRankOverride", &CceCommissions::SetRankOverride, (Arg("rank")));

//    Class rb_cBonus = define_class<CceBonus>("CeBonus")
//        .define_constructor(Constructor<CceBonus>())
//        .define_method("Add", &CceBonus::Add, (Arg("system_id"), Arg("user_id"), Arg("amount"), Arg("bonus_date")))
//        .define_method("Edit", &CceBonus::Edit, (Arg("system_id"), Arg("bonus_id"), Arg("user_id"), Arg("amount"), Arg("bonus_date")))
//        .define_method("Query", &CceBonus::Query, (Arg("system_id")))
//        .define_method("QueryUser", &CceBonus::QueryUser, (Arg("system_id"), Arg("user_id")))
//        .define_method("Disable", &CceBonus::Disable, (Arg("system_id"), Arg("bonus_id")))
//        .define_method("Enable", &CceBonus::Enable, (Arg("system_id"), Arg("bonus_id")));

//    Class rb_cPayout = define_class<CcePayout>("CePayout")
//        .define_constructor(Constructor<CcePayout>())
//        .define_method("Query", &CcePayout::Query, (Arg("system_id"), Arg("authorized")))
//        .define_method("Auth", &CcePayout::Auth, (Arg("system_id"), Arg("grand_id"), Arg("authorized")))
//        .define_method("AuthBulk", &CcePayout::AuthBulk, (Arg("system_id")))
//        .define_method("Disable", &CcePayout::Disable, (Arg("system_id"), Arg("grand_id")))
//        .define_method("Enable", &CcePayout::Enable, (Arg("system_id"), Arg("grand_id")));

//    Class rb_cBankAccount = define_class<CceBankAccount>("CeBankAccount")
//        .define_constructor(Constructor<CceBankAccount>())
//        .define_method("Add", &CceBankAccount::Add, (Arg("system_id"), Arg("user_id"), Arg("account_type"), Arg("routing_number"),
//            Arg("account_number"), Arg("holder_name")))
//        .define_method("Edit", &CceBankAccount::Edit, (Arg("system_id"), Arg("user_id"), Arg("account_type"), Arg("routing_number"),
//            Arg("account_number"), Arg("holder_name")))
//        .define_method("Query", &CceBankAccount::Query, (Arg("system_id")))
//        .define_method("Disable", &CceBankAccount::Disable, (Arg("system_id"), Arg("user_id")))
//        .define_method("Enable", &CceBankAccount::Enable, (Arg("system_id"), Arg("user_id")))
//        .define_method("InitiateValidation", &CceBankAccount::InitiateValidation, (Arg("system_id"), Arg("user_id")))
//        .define_method("Validate", &CceBankAccount::Validate, (Arg("system_id"), Arg("user_id"), Arg("amount1"), Arg("amount2")));

//    Class rb_cPayments = define_class<CcePayments>("CePayment")
//        .define_constructor(Constructor<CcePayments>())
//        .define_method("SetPaymentType", &CcePayments::SetPaymentType, (Arg("system_id"), Arg("payment_type")))
//        .define_method("Process", &CcePayments::Process, (Arg("system_id"), Arg("batch_id")))
//        .define_method("QueryUser", &CcePayments::QueryUser, (Arg("system_id"), Arg("user_id")))
//        .define_method("QueryBatch", &CcePayments::QueryBatch, (Arg("system_id"), Arg("batch_id")))
//        .define_method("QueryNoPay", &CcePayments::QueryNoPay, (Arg("system_id"), Arg("batch_id")));
}

// End of COMPILE_RICE //
#endif 