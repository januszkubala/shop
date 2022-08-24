// **************************************************************************
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Photo gallery
// © 2022 Janusz Kubala
// Version 1.0
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// **************************************************************************

document.querySelectorAll(".media-gallery").forEach(gallery => {

    // Create modal for viewing files
    let fileModal = document.createElement("div")
    fileModal.classList.add("media-modal")
    // Hide modal
    fileModal.style.display = "none"

    document.body.prepend(fileModal)

    let filePreview = gallery.querySelector(".media-preview")
    let thumbnailsList = gallery.querySelector(".media-files")
    let items = thumbnailsList.querySelectorAll(".media-item")
    let defaultFile = items[0]

    let previewItem = document.createElement("a")
    previewItem.href = defaultFile.querySelector("a").href

    filePreview.replaceChildren(previewItem)

    let events = ["click", "mouseover"]

    let files = []

    if(items.length > 0) {
        
        for(let i = 0; i < items.length; i++) {

            let file = {}
            file.file = items[i].querySelector("a").href
            file.src = items[i].querySelector(".media-thumbnail").src
            file.type = items[i].dataset.type
            file.title = items[i].dataset.title
            file.description = items[i].dataset.description

            files.push(file)

            events.forEach(function(event) {

                items[i].addEventListener(event, (e) => {

                    e.preventDefault()

                    let fileSrc = items[i].querySelector("a").href

                    let previewImage = document.createElement("img")
                    previewImage.src = fileSrc
                    previewItem.href = fileSrc

                    previewItem.replaceChildren(previewImage)

                    previewImage.addEventListener("mouseover", (e) => {
                
                        e.preventDefault()

                        gallery.style.position = "relative"

                        // Get image dimensions
                        let previewWidth = previewImage.width
                        let previewHeight = previewImage.height

                        let originalWidth = previewImage.naturalWidth
                        let originalHeight = previewImage.naturalHeight
                        
                        // Get zoom ratios
                        let zoomFactorX = originalWidth / previewWidth
                        let zoomFactorY = originalHeight / previewHeight
                        
                        let previewContainer = document.createElement("div")
                        let previewContainerWidth = 400
                        let previewContainerHeight = 400

                        let navigatorWidth = previewContainerWidth / zoomFactorX
                        let navigatorHeight = previewContainerHeight / zoomFactorY
                        

                        // Set preview container and add some styles
                        previewContainer.classList.add("media-preview-container")
                        previewContainer.style.position = "absolute"
                        previewContainer.style.width = previewContainerWidth + "px"
                        previewContainer.style.height = previewContainerHeight + "px"
                        previewContainer.style.right = (0 - previewContainerWidth - 20) + "px"
                        previewContainer.style.backgroundImage = "url('" + fileSrc + "')"
                        
                        previewItem.appendChild(previewContainer)

                        // Add navigator to the image
                        let navigator = document.createElement("div")
                        navigator.classList.add("media-navigator")
                        navigator.style.position = "absolute"
                        navigator.style.width = navigatorWidth + "px"
                        navigator.style.height = navigatorHeight + "px"
                        navigator.style.left = 0
                        navigator.style.top = 0
                        navigator.style.pointerEvents = "none"
                
                        previewItem.appendChild(navigator)
                
                    })

                    previewImage.addEventListener("mouseleave", (e) => {
                        
                        let previewContainer = gallery.querySelector(".media-preview-container")

                        // Get navigator
                        let navigator = gallery.querySelector(".media-navigator")

                        previewContainer.remove()
                        navigator.remove()

                    })

                    previewImage.addEventListener("mousemove", (e) => {

                        // Get navigator
                        let navigator = gallery.querySelector(".media-navigator")

                        // Get image dimensions
                        let previewWidth = previewImage.width
                        let previewHeight = previewImage.height

                        let originalWidth = previewImage.naturalWidth
                        let originalHeight = previewImage.naturalHeight
                        
                        // Get zoom ratios
                        let zoomFactorX = originalWidth / previewWidth
                        let zoomFactorY = originalHeight / previewHeight

                        // Get mouse position XY
                        let mousePosX = e.clientX + window.pageXOffset
                        let mousePosY = e.clientY + window.pageYOffset
                        
                        const bodyRect = document.body.getBoundingClientRect()
                        const rect = previewImage.getBoundingClientRect()

                        // Get mouse position relative to preview
                        const posX = Math.round(mousePosX - rect.left + bodyRect.left)
                        const posY = Math.round(mousePosY - rect.top + bodyRect.top)

                        let coordX = posX - navigator.offsetWidth + navigator.offsetWidth / 2
                        let coordY = posY - navigator.offsetHeight + navigator.offsetHeight / 2
                        
                        // If X coordinate goes less than 1 pixel then set it to 1
                        if(coordX < 1) {
                            coordX = 1
                        }
                        else if(coordX > (previewImage.offsetWidth - navigator.offsetWidth)) {
                            coordX = previewImage.offsetWidth - navigator.offsetWidth
                        }
                        
                        // If Y coordinate goes less than 1 pixel then set it to 1
                        if(coordY < 1) {
                            coordY = 1
                        }
                        else if(coordY > (previewImage.offsetHeight - navigator.offsetHeight)) {
                            coordY = previewImage.offsetHeight - navigator.offsetHeight - 1
                        }
                        
                        navigator.style.left = coordX + "px"
                        navigator.style.top = coordY + "px"

                        let previewContainer = document.querySelector(".media-preview-container")
                        
                        // Move zoomed picture as mouse moves over its preview
                        previewContainer.style.backgroundPositionX = 0 - coordX * zoomFactorX + 3 + "px"
                        previewContainer.style.backgroundPositionY = 0 - coordY * zoomFactorY + 2 + "px"

                    })

                    previewImage.addEventListener("click", (e) => {

                        e.preventDefault()

                        showFile(fileModal, previewImage.src, files, items[i].dataset.title, items[i].dataset.description)

                    })
                    
                })
            })
        }
    }

    items[0].dispatchEvent(new Event("click"))

})

function showFile(modal, src, files, title, description) {

    modal.style.display = "block"

    let modalGrid = document.createElement("div")
    modalGrid.classList.add("modal-grid")
    modal.appendChild(modalGrid)

    let modalContent = document.createElement("div")
    modalContent.classList.add("modal-content")
    modalGrid.appendChild(modalContent)

    let modalContentView = document.createElement("div")
    modalContentView.classList.add("modal-content-view")
    modalContent.appendChild(modalContentView)

    let modalContentDescription = document.createElement("div")
    modalContentDescription.classList.add("modal-content-description")
    modalContentDescription.textContent = "Foobar"
    modalContent.appendChild(modalContentDescription)

    let modalMenu = document.createElement("div")
    modalMenu.classList.add("modal-menu")
    modalGrid.appendChild(modalMenu)

    modalContentView.style.backgroundImage = "url('" + src +"')"
    
    modalContentDescription.innerHTML = ""
    modalContentDescription.style.padding = 0

    // Add title
    if(title != null) {
        let fileTitle = document.createElement("h3")
        fileTitle.textContent = title
        fileTitle.style.margin = 0
        modalContentDescription.appendChild(fileTitle)
    }

    // Add description
    if(description != null) {
        let fileDescription = document.createElement("p")
        fileDescription.textContent = description
        fileDescription.style.margin = 0
        modalContentDescription.appendChild(fileDescription)
    }

    if(title != null || description != null) {
        modalContentDescription.style.padding = "2rem"
    }

    let imagesThumbnails = document.createElement("div")
    imagesThumbnails.classList.add("media-thumbnails-images")
    modalMenu.appendChild(imagesThumbnails)

    files.forEach(file => {

        let fileThumbnailLink = document.createElement("a")
        fileThumbnailLink.classList.add("media-item")
        fileThumbnailLink.dataset.title = file.title
        fileThumbnailLink.dataset.description = file.description
        fileThumbnailLink.href = file.file

        let fileThumbnail = document.createElement("img")
        fileThumbnail.src = file.src
        fileThumbnailLink.appendChild(fileThumbnail)

        imagesThumbnails.appendChild(fileThumbnailLink)

    })

}