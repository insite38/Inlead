/**
 * Created by latishevAD on 11.10.2017.
 */
var myDropzone = $("#my-awesome-dropzone").data('max-size');

$(document).ready(function () {
    // $("div#myid, div#myid .inner_fallback, div#myid .inner_fallback p").dropzone({
    //     url: "/admin/gallery/upload.php"
    // });
    var objResponse = {};
    var iO = 0;
    var myDrop = $("#my-awesome-dropzone").dropzone({
        url: "/admin/gallery/upload.php",
        maxFilesize : myDropzone,
        maxFiles: 100,
        acceptedFiles: ".jpeg,.jpg,.png",
        addRemoveLinks: "dictRemoveFile, dictCancelUploadConfirmation, dictCancelUpload",
        previewsContainer: "#previws",
        dictDefaultMessage: "Нажмите или перетащите сюда файлы для загрузки",
        dictRemoveFile: "Удалить",
        init: function () {
            this.on("success", function (file, xhr) {
                iO ++;
                parseXhr = JSON.parse(xhr);
                objResponse[file.name] = JSON.parse(xhr);
            });
            this.on("removedfile", function (file) {
                delElement(file,objResponse);
            })
        }
    });

    $("#test").on('click', function () {
        console.log(objResponse);
    });


    function delElement(file, allItems) {
        console.log(allItems[file.name].img.idImg + ' - Будет удалён');
        $.get(
            '/admin/gallery/uploadDel.php',
            {id: allItems[file.name].img.idImg},
            function(req, status, xhrReq){
                console.log(req);
                console.log(status);
            }
        );
        delete allItems[file.name];
    }
});
