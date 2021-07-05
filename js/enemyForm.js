$(document).ready(function(){
    'use strict';
    function validateForm(e){
        let button = $(document.activeElement).attr('name');
        errorMessage.text("");
        if(button == "save"){
            let valid = true;
            if(/^\w{1,20}$/.test(name.val()) == false || /^\w{1,20}$/.test(sprite.val()) == false){
                valid = false;
                errorMessage.append("Name and Sprite must be between 1 and 20 alphanumeric characters. ");
            }
            if(isNaN(health.val()) || health.val() <= 0 || health.val() > 10000){
                valid = false;
                errorMessage.append("Health must be a number greater than zero. ");
            }
            if(isNaN(speed.val()) || speed.val() <= 0 || speed.val() > 10000){
                valid = false;
                errorMessage.append("Speed must be a number greater than zero.");
            }
            if(valid === false){
                e.preventDefault();
            }
        }
    }
    let name = $("#name");
    let sprite = $("#sprite");
    let health = $("#health");
    let speed = $("#speed");
    let errorMessage = $("#warning");
    $("#enemyForm").submit(function(e){
        validateForm(e);
    });
});