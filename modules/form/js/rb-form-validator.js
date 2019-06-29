// =========================================================================
// RB FORM VALIDATION FRAMEWORK
// =========================================================================
class RB_Field_Validator{
    options = {
        stopOnFirstError: true, //Wheter to continue checking for errors after one had been found
        checkEmpty: true, //Check if the value is ''
        emptyMessage: 'Es obligatorio completar este campo', //Error message for empty case
        updateOn: 'input',//Update the validator when this events runs on the $field
    };

    /**
    *   @Args
    *   @Param  $field              jQuery object of the form
    *       @Type jQuery
    *   @Param  settings            Array of options to overwrite the defaults
    *       @Type Array
    */
    constructor($field, settings){
        this.$field = $field;
        this.checks = [];
        this.isValid = false;
        this.lastError = '';
        this.options = typeof settings === 'object' ? Object.assign({}, this.options, settings) : this.options;
        this.attachEvents();
        return this;
    }

    //Attach the events of 'updateOn' to the $field. Runs updateStatus when the events fire
    attachEvents(){
        var updateOn = this.getOption('updateOn');
        if( typeof updateOn != 'string' )
            return false;
        var validator = this;
        this.$field.on(updateOn, function(){
            validator.updateStatus();
        });
    }

    //Adds an error check with an error message. {check: function(value,this), message: ''}
    addCheck(checkData){
        if(checkData.hasOwnProperty('check') && typeof checkData.check === 'function' && checkData.hasOwnProperty('message'))
            this.checks.push(checkData);
        return this;
    }

    //Goes to every check added and sets de validity of the field accordingly
    updateStatus(){
        var value = this.$field.val();
        var result = true;
        var validator = this;

        if(this.getOption('checkEmpty') && value == ''){
            result = false;
            validator.lastError = this.getOption('emptyMessage');
        }
        if( result || this.getOption('stopOnFirstError') ){
            for(let i = 0; i < this.checks.length; i++){
                let checkData = this.checks[i];
                let hasError = checkData.check(value, validator);
                if(result && hasError){
                    result = false;
                    validator.lastError = checkData.message;
                    if(this.getOption('stopOnFirstError'))
                        break;
                }
            }
        }
        this.isValid = result;
        this.afterUpdate();
    }

    //Runs the onUpdate function, if it exists
    afterUpdate(){
        var afterUpdateFunction = this.getOption('onUpdate');
        if(typeof afterUpdateFunction === 'function')
            afterUpdateFunction(this);
    }

    //Checks every native validations from field.validity
    //Returns the code of the firt one it encounters
    getNativeError(){
        var validity = this.$field[0].validity;
        var errorCode = '';
        if(validity.valid)
            return errorCode;
        for(var error in validity){
            if(error){
                errorCode = error;
                break;
            }
        }
        return errorCode;
    }

    //Returns an options from this.options
    getOption(name){
        return this.options[name];
    }
}

class RB_Form_Validator{

    constructor($form){
        this.$form = $form;
        this.fields = [];
        this.isValid = false;
        this.stopOnFirstError = false;
        return this;
    }

    updateStatus(){
        var result = true;
        for(let i = 0; i < this.fields.length; i++){
            let fieldValidator = this.fields[i];
            fieldValidator.updateStatus();
            if(!fieldValidator.isValid){
                result = false;
                if(this.stopOnFirstError)
                    break;
            }
        }
        this.isValid = result;
    }

    addField(field){
        if(field instanceof RB_Field_Validator)
            this.fields.push(field);
        return this;
    }
}
