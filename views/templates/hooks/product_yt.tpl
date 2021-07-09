{**
* NOTICE OF LICENSE
*
* This source file is subject to License,
* that is bundled with this package in the file LICENSE.txt.
* If you did not receive a copy of the license, please send an email
* to connie@diacalc.org so we can send you a copy immediately.
*
* Do not edit or add to this file.
* @author    Konstantin Toporov
* @copyright © 2019 Konstantin Toporov
* @license   LICENSE.txt
* @category  Front Office Features
*}

<div>
    <h3>{l s='Youtube settings' mod='kt_youtubeproduct'}</h3>
    <div class="form-group">
        <label for="kt_yt_reference">{l s='Youtube ID' mod='kt_youtubeproduct'}:</label>
        <input type="text" class="form-control" id="kt_yt_reference"
            name="kt_yt_reference" value="{$kt_reference}">
        <span class="help-block">{l s='ID of the youtube video. It can look like 1TWjzSnzvRM. Specify it if you want to show youtube video, otherwise leave it blank.' mod='kt_youtubeproduct'}</span>
    </div>
    <div class="form-group">
        <label for="kt_yt_params">{l s='Addtional parameters' mod='kt_youtubeproduct'}:</label>
        <input type="text" class="form-control" id="kt_yt_params"
            name="kt_yt_params" value="{$kt_params}">
        <span class="help-block">{l s='This is optional.' mod='kt_youtubeproduct'}</span>
    </div>
</div>
