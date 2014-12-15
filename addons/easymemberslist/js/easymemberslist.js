function easymemberslist_filter()
{
    var sort_order = $('#sort_order :selected').val();
    var min_level = $('#min_level').val();
    var max_level = $('#max_level').val();
    var classid = $('#class_filter').val();
    var raceid = $('#race_filter').val();
    var guildrank = $('#guildrank').val();
    var where = 'm.level >= '+min_level+' AND m.level <= '+max_level;
    if (classid != 999) where += ' AND m.classid = '+classid;
    if (raceid != 999) where += ' AND p.raceid = '+raceid;
    if (guildrank != 999) where += ' AND m.guild_rank = '+guildrank;
    $("#sort_order option").removeAttr('selected');
    $("#sort_order option[value='"+sort_order+"']").attr('selected',true);
    $("#race_filter option").removeAttr('selected');
    $("#race_filter option[value='"+raceid+"']").attr('selected',true);
    $("#class_filter option").removeAttr('selected');
    $("#class_filter option[value='"+classid+"']").attr('selected',true);
    $("#guildrank option").removeAttr('selected');
    $("#guildrank option[value='"+guildrank+"']").attr('selected',true);
    $('#members').empty();
    easymemberslist_get_membersdata(sort_order, where);     
}

function easymemberslist_resetfilter()
{
    $('#min_level').val(1);
    $('#max_level').val(90);
    $('#sort_order option').removeAttr('selected');
    $("#sort_order option[value='m.name ASC']").attr('selected',true);
    $("#race_filter option").removeAttr('selected');
    $("#race_filter option[value='999']").attr('selected',true);
    $('#class_filter option').removeAttr('selected');
    $("#class_filter option[value='999']").attr('selected',true);
    $('#guildrank option').removeAttr('selected');
    $("#guildrank option[value='999']").attr('selected',true);
    easymemberslist_filter();
}   

function easymemberslist_get_membersdata(order_by, where_filter)
{   
   $(document).ready(function()
   { 
        $.get("media.xml", { p:"guild-easymemberslist", action:"get_memberslist", sort_order:order_by, where:where_filter  },  function(data)
        { 
            $('#members').empty();
            $('#members').append(data); 
        }, "html");
   });                
}