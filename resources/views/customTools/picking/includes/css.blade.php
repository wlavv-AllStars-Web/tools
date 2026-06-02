<style>
    .pickingHeaderButton{   cursor: pointer;border: 1px solid #aaa; width: 22%; margin: 5px;float: left;border-radius: 5px; }
    .picking_preparation{   background-color: #048dcd; }
    .picking_partial{       background-color: #956f55; }
    .picking_warranty{      background-color: #EBC5E0; }
    .picking_info{          background-color: #acacac; }
    .pickingHeaderButton > div{ font-size: 26px; text-align: center; font-weight: bolder;color: #FFF; }
    .holders{ width: 100%; }
    .holders > div.alert{ margin-bottom: 0; text-align: center; }
    
    #scan{ width: 100%; border: 1px solid #ccc; text-align: center; border-radius: 5px; }
    
    .specialPanel{ background-color: #fff; border: 1px solid #ddd; border-radius: 5px; padding: 10px !important; margin-top: 10px; }

    .picking-store-switcher{
        display: flex;
        gap: 8px;
        width: 100%;
        align-items: stretch;
        flex-wrap: nowrap;
    }

    .picking-store-badge{
        width: 50%;
        border: 0;
        border-radius: 5px;
        color: #fff;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        min-height: 48px;
        font-weight: 700;
        text-align: center;
    }

    .picking-store-badge span{
        font-size: 18px;
    }

    .picking-store-badge strong{
        min-width: 30px;
        padding: 3px 8px;
        border-radius: 15px;
        background: #fff;
        color: #333;
        font-size: 15px;
        line-height: 1.2;
    }

    .picking-store-badge-asm{ background-color: red; }
    .picking-store-badge-asd{ background-color: dodgerblue; }
    
    .order_container > table > tbody > tr{ height: 40px; }
    
    
    
    
        #orderSupportContainer{ width: 100%; display: none; }
        .productSupportContainer{ width: 100%; display: none; }
    
    
    
    
    @if( auth()->user()->id != 43)
        #orderSupportContainer{ width: 100%; display: none; }
        .productSupportContainer{ width: 100%; display: none; }
    @endif


    .blurData{ color: transparent; text-shadow: 0 0 5px rgba(0,0,0,0.8); }
    
    /** #headerBreadcrumbsContainer{ display: none; } **/
</style>
