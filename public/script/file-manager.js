// **************************************************************************
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Drag and drop HTML5 AJAX files uploader
// © 2022 Janusz Kubala
// Version 1.0
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// **************************************************************************

// Required elements for the uploader
let fileManagerUploader = document.getElementById("file-manager-uploader")
let filesUploaded = document.getElementById("files-uploaded")
let inputFile = document.getElementById("file")
let fileDefault = document.getElementById("file-default")
let dropArea = document.getElementById("drop-area")
let dragText = document.getElementById("drag-text")
let dropQueue = document.getElementById("drop-queue")

// When file(s) are uploaded via button
inputFile.addEventListener("change", (e) => {

    const files = inputFile.files
    const filesArray = Array.from(files)

    filesArray.forEach(file => {
        createFile(file, dropQueue)
    })

})

// When file(s) are dragged over the uploader
dropArea.addEventListener("dragover", (e) => {
    e.preventDefault()
    dropArea.classList.add("active")
    dragText.textContent = dragText.dataset.textdragactive;
})

// When dropping is aborted
dropArea.addEventListener("dragleave", () => {
    dropArea.classList.remove("active")
    dragText.textContent = dragText.dataset.textdrag;
})

// When file(s) are dropped to the uploader
dropArea.addEventListener("drop", (e) => {
    e.preventDefault()
    
    const files = e.dataTransfer.files
    const filesArray = Array.from(files)

    filesArray.forEach(file => {
        createFile(file, dropQueue)
    })

})

// Function to truncate long strings keeping leading and trailing chunks
// with ellipsis in between

function truncate(string, maxLength, separator) {

    if (string.length <= maxLength) {
        return string
    }

    separator = separator || '...'
    
    let separatorLength = separator.length,
        charsToShow = maxLength - separatorLength,
        frontChars = Math.ceil(charsToShow/2),
        rearChars = Math.floor(charsToShow/2)
    
    return string.substr(0, frontChars) + 
           separator + 
           string.substr(string.length - rearChars)
    
}

// **************************************************************************
// File CRUD functions
// **************************************************************************

// POST method
function createFile(file, dropQueue) {

    let extensions = {
        "image/jpeg": "jpg",
        "image/png": "png",
        "image/gif": "gif",
        "application/pdf": "pdf"
    }

    if(typeof extensions[file.type] !== "undefined") {

        let assetsIconsDir = fileManagerUploader.dataset.assetsiconsdir
        let assetsLogosDir = fileManagerUploader.dataset.assetslogosdir
        let extension = extensions[file.type]

        // Build DOM elements for each file
        let fileItem = document.createElement("div")
        fileItem.classList.add("file-item", "col-3", "mb-3", "text-center", "position-relative")
        let fileIcon = document.createElement("img")
        fileIcon.classList.add("file-view", "mb-3")
        fileIcon.src = assetsIconsDir + extension + ".svg"
        fileIcon.alt = ""
        let fileProgress = document.createElement("div")
        fileProgress.classList.add("progress", "mb-2")
        fileProgress.style.height = "1em"
        let fileProgressBar = document.createElement("div")
        fileProgressBar.classList.add("progress-bar")
        let fileName = document.createElement("div")
        fileName.classList.add("small", "text-muted")
        fileName.innerText = truncate(file.name, 25)

        let fileMenu = document.createElement("div")
        fileMenu.classList.add("file-menu", "position-absolute")
        let buttonDefault = document.createElement("button")
        buttonDefault.classList.add("btn", "btn-sm", "btn-dark")
        var icon = document.createElement("i")
        icon.classList.add("fa-solid", "fa-eye", "fa-fw")


        // If input for default element exists, turn this feature on
        // otherwise ignore
        if(buttonDefault.length > 0) {
            buttonDefault.appendChild(icon)
            buttonDefault.classList.add("disabled")
            buttonDefault.disabled = true
            fileMenu.appendChild(buttonDefault)
        }

        let buttonDelete = document.createElement("button")
        buttonDelete.classList.add("btn", "btn-sm", "btn-dark")
        var icon = document.createElement("i")
        icon.classList.add("fa-solid", "fa-times", "fa-fw")
        buttonDelete.appendChild(icon)

        // Inputs to set title, alt attribute and file description
        // which may be used somewhere in its template

        let fileForm = document.createElement("div")
        fileForm.classList.add("mt-2")

        let inputTitle = document.createElement("input")
        inputTitle.classList.add("form-control", "form-control-file", "form-control-xsm", "mb-1")
        inputTitle.placeholder = fileManagerUploader.dataset.texttitle
        fileForm.appendChild(inputTitle)


        let inputDescription = document.createElement("textarea")
        inputDescription.cols = 30
        inputDescription.rows = 2
        inputDescription.classList.add("form-control", "form-control-file", "form-control-xsm", "mb-1")
        inputDescription.placeholder = fileManagerUploader.dataset.textdescription
        fileForm.appendChild(inputDescription)

        let inputAlt = document.createElement("input")
        inputAlt.disabled = true
        inputAlt.classList.add("form-control", "form-control-file", "form-control-xsm")
        inputAlt.placeholder = fileManagerUploader.dataset.textalt
        fileForm.appendChild(inputAlt)

        let buttonGroup = document.createElement("div")
        buttonGroup.classList.add("mt-2", "text-end")
        
        let buttonPut = document.createElement("button")
        buttonPut.classList.add("btn", "btn-secondary", "btn-sm")
        buttonGroup.appendChild(buttonPut)

        var icon = document.createElement("i")
        icon.classList.add("fa-solid", "fa-floppy-disk", "fa-fw")
        buttonPut.appendChild(icon)

        fileMenu.appendChild(buttonDelete)
        fileItem.appendChild(fileIcon)
        fileItem.appendChild(fileProgress)
        fileProgress.appendChild(fileProgressBar)
        fileItem.appendChild(fileName)
        dropQueue.appendChild(fileItem)
        fileItem.appendChild(fileMenu)
        fileItem.appendChild(fileForm)
        fileItem.appendChild(buttonGroup)

        let formData = new FormData()
        formData.append("file", file)

        let request = new XMLHttpRequest()
        request.open("POST", "/file/create")
        request.upload.addEventListener("progress", function(e) {
            
            let percentTotal = (e.loaded / e.total) * 100
            fileProgressBar.style.width = percentTotal + "%"
            
        })
        request.addEventListener("load", function(e) {

            let file = JSON.parse(request.response)

            buttonDelete.dataset.id = file.id
            buttonPut.dataset.id = file.id

            inputTitle.id = "file-title-" + file.id
            inputDescription.id = "file-description-" + file.id
            inputAlt.id = "file-alt-" + file.id

            // If it's an image, display the thumbnail
            if((file.mimeType).startsWith("image/") === true) {

                // Enable default button as only image can be a default file
                buttonDefault.classList.remove("disabled")
                buttonDefault.disabled = true

                // Enable alt input (also applies to images only)
                inputAlt.disabled = false
                
                // If it is a local file
                if(file.source == "local_cdn") {
                    // Get local uploads directory
                    let localCDNDir = fileManagerUploader.dataset.assetslocalcdn
                    // Fetch and set thumbnail
                    fileIcon.src = localCDNDir + file.fileName + "_thumb." + file.extension
                    fileIcon.classList.add("rounded")
                }

            }

            // Add event listener to the delete button
            buttonDelete.addEventListener("click", (e) => {
                
                let id = buttonDelete.dataset.id
                deleteFile(id, buttonDelete, fileItem)

            })

            // Add event listener to the update button
            buttonPut.addEventListener("click", (e) => {
                
                let id = buttonDelete.dataset.id
                putFile(id, buttonPut, fileItem)

            })

            fileForm.querySelectorAll(".form-control-file").forEach(item => {
                
                item.addEventListener("change", (e) => {

                    item.classList.add("border-warning")

                })

            })

            let cdnIcon = document.createElement("img")
            cdnIcon.classList.add("position-absolute", "cdn-provider", "bg-light", "rounded", "p-1", "border")
            cdnIcon.src = assetsLogosDir + "480x240-amazon-web-services.svg"
            fileItem.appendChild(cdnIcon)

            let fileHelper = document.createElement("input")
            fileHelper.type = "hidden"
            fileHelper.id = "files-" + file.id
            fileHelper.name = "files[]"
            fileHelper.value = file.id

            filesUploaded.appendChild(fileHelper)


        })
        request.send(formData)

    }

}

// DELETE method
function deleteFile(id, button, item) {

    // Create loading icon
    let loadingIcon = document.createElement("i")
    loadingIcon.classList.add("fa-solid", "fa-circle-notch", "fa-spin", "fa-fw")

    // Disable delete button
    button.disabled = true
    button.classList.add("disabled")

    // Replace current icon
    button.replaceChildren(loadingIcon)
    
    // Send DELETE request
    let request = new XMLHttpRequest()
    request.open("DELETE", "/file/delete/" + id)
    request.addEventListener("load", function(e) {

        // Fetch JSON response
        let response = JSON.parse(request.response)

        if(response.success) {
            // Remove file if the file is removed
            item.remove()
            // Remove file helper if it was set
            let helper = document.getElementById("files-" + id)
            if(typeof helper !== "undefined") {
                helper.remove()
            }
        }

    })
    request.send()

}

// PUT method
function putFile(id, button, item) {

    // Create loading icon
    let loadingIcon = document.createElement("i")
    loadingIcon.classList.add("fa-solid", "fa-circle-notch", "fa-spin", "fa-fw")

    // Disable delete button
    button.disabled = true
    button.classList.add("disabled")

    // Replace current icon
    button.replaceChildren(loadingIcon)


    inputTitle = document.getElementById("file-title-" + id)
    inputDescription = document.getElementById("file-description-" + id)
    inputAlt = document.getElementById("file-alt-" + id)
    
    let payload = {}
    payload.title = inputTitle.value
    payload.description = inputDescription.value
    payload.alt = inputAlt.value

    let json = JSON.stringify(payload)
    
    // Send PUT request
    let request = new XMLHttpRequest()
    request.open("PUT", "/file/put/" + id)
    request.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
    request.addEventListener("load", function(e) {

        // Fetch JSON response
        let response = JSON.parse(request.response)

        let icon = document.createElement("i")
        icon.classList.add("fa-solid", "fa-floppy-disk", "fa-fw")
        button.replaceChildren(icon)
        button.classList.remove("disabled")
        button.disabled = false

        if(response.success) {

            inputTitle.classList.remove("border-warning")
            inputDescription.classList.remove("border-warning")
            inputAlt.classList.remove("border-warning")
            
        }
        else {
            // ...
        }

    })
    request.send(json)

}