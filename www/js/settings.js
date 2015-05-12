/**
 * @author Stefan Fixl
 */

/**
 * Add the "add new parameter" - functionality to the plus button
 */
$(".ui-icon-circle-plus").click(function (){
    settings = $(this).parent("td");
    input = settings.children(".input-params-div");
    newinput = input.html();
    //<input type="text" class="input-parameters" name="parametersR2[]" value="t<=1440">
    index = newinput.search("value") + 6;
    if(newinput.charAt(index) != '"'){
        alert("newinput.charAt(index) != '\"'");
    }else{
        index2 = index+1;
        while(newinput.charAt(index2) != '"'){
            index2++;
        }
        newinput2 = newinput.substr(0, index+1) + newinput.substr(index2, newinput.length);
        newinput = newinput2;
    }
    input.last().append("<div class='.input-params-div'>" + newinput + "</div>");
});
