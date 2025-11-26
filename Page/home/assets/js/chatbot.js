$(document).ready(function () {
  console.log("🚀 Chatbot JS Starting...");

  // ✅ DEBUG CURRENT PAGE INFO
  console.log("📍 Current page info:", {
    pathname: window.location.pathname,
    href: window.location.href,
    host: window.location.host,
  });

  // ✅ KHAI BÁO BIẾN TOÀN CỤC
  let chatMessages, chatInput, sendBtn, chatProducts;
  let isDragging = false;
  let isFloatingBtnDragging = false;
  let hasMouseMoved = false;
  let dragOffset = { x: 0, y: 0 };
  let floatingBtnOffset = { x: 0, y: 0 };
  let isUserLoggedIn = false;
  let chatContainer, chatModal, floatingBtn, floatingWrapper;

  // ✅ XÁC ĐỊNH BASE PATH - FIX LOGIC
  const basePath = window.CHATBOT_BASE_PATH || "./";

  // ✅ XÁC ĐỊNH API BASE PATH - SỬA LẠI HOÀN TOÀN
  function getApiBasePath() {
    const currentPath = window.location.pathname;
    const currentHref = window.location.href;

    console.log("🔍 Current pathname:", currentPath);
    console.log("🔍 Current href:", currentHref);

    let apiBase = "";

    // ✅ KIỂM TRA DỰA TRÊN URL THỰC TẾ
    if (
      currentPath.includes("/Page/cart/") ||
      currentHref.includes("/Page/cart/")
    ) {
      apiBase = "../../api/";
    } else if (
      currentPath.includes("/Page/products/") ||
      currentHref.includes("/Page/products/")
    ) {
      apiBase = "../../api/";
    } else if (
      currentPath.includes("/Page/user/") ||
      currentHref.includes("/Page/user/")
    ) {
      apiBase = "../../api/";
    } else if (
      currentPath.includes("/Page/search/") ||
      currentHref.includes("/Page/search/")
    ) {
      apiBase = "../../api/";
    } else if (
      currentPath.includes("/Page/home/") ||
      currentHref.includes("/Page/home/")
    ) {
      apiBase = "../../../api/";
    } else {
      // Root hoặc index - FALLBACK
      apiBase = "./api/";
    }

    console.log("🚀 API Base Path determined:", apiBase);

    // ✅ TEST API PATH - Thêm fallback testing
    const testUrl = apiBase + "check_session.php";
    console.log("🧪 Testing API path:", testUrl);

    return apiBase;
  }

  const apiBasePath = getApiBasePath();

  console.log("🔍 Using Base Paths:", {
    basePath: basePath,
    apiBasePath: apiBasePath,
  });

  // ✅ STORAGE KEY TOÀN CỤC
  const CHAT_STORAGE_KEY = "ai_chat_global_history";
  const CHAT_POSITION_KEY = "ai_chat_position";
  const FLOATING_BTN_POSITION_KEY = "ai_floating_btn_position";

  // ✅ DELAY KHỞI TẠO
  setTimeout(function () {
    initializeChatbot();
  }, 500);

  function initializeChatbot() {
    console.log("🔧 Initializing chatbot...");

    // ✅ KHỞI TẠO CÁC PHẦN TỬ
    chatMessages = $("#ai-chat-messages");
    chatInput = $("#ai-chat-input");
    sendBtn = $("#ai-chat-send");
    chatProducts = $("#ai-chat-products");
    chatContainer = $("#ai-chat-draggable");
    chatModal = $("#ai-chatbot-modal");
    floatingBtn = $("#open-ai-chat");
    floatingWrapper = $(".ai-chat-floating-wrapper");

    if (chatMessages.length === 0) {
      console.error("❌ Không tìm thấy chatbot elements!");
      return false;
    }

    console.log("✅ Chatbot elements found:", {
      messages: chatMessages.length,
      input: chatInput.length,
      sendBtn: sendBtn.length,
      products: chatProducts.length,
      container: chatContainer.length,
      floatingBtn: floatingBtn.length,
      floatingWrapper: floatingWrapper.length,
    });

    // ✅ SETUP EVENT HANDLERS
    setupEventHandlers();

    // ✅ KIỂM TRA ĐĂNG NHẬP
    checkLoginStatus();

    // ✅ KHÔI PHỤC VỊ TRÍ
    restoreChatPosition();
    restoreFloatingBtnPosition();

    // ✅ TẢI LỊCH SỬ CHAT - ƯU TIÊN CHO PRODUCTS PAGE
    const isProductsPage = window.location.pathname.includes("/Page/products/");

    setTimeout(() => {
      console.log("📥 Starting chat history load...");
      loadGlobalChatHistory();

      // ✅ THÊM CALLBACK ĐẶC BIỆT CHO PRODUCTS PAGE
      if (isProductsPage) {
        setTimeout(() => {
          console.log("🔍 Products page - checking chat sync...");

          const messagesCount = chatMessages.find(".ai-message").length;
          const productsVisible = chatProducts.is(":visible");
          const productsCount = chatProducts.find(".ai-product-card").length;

          console.log("📊 Chat status:", {
            messages: messagesCount,
            productsVisible: productsVisible,
            productsCount: productsCount,
          });

          // Nếu không có history thì tạo welcome message
          if (messagesCount <= 1) {
            console.log(
              "ℹ️ No chat history, showing welcome for products page"
            );
            showProductsPageWelcome();
          }
        }, 2000);
      }
    }, 1000);

    // ✅ HIỂN THỊ GREETING THEO TRANG
    if (isProductsPage) {
      setTimeout(() => {
        $("#ai-chat-greeting")
          .text("Cần tư vấn sản phẩm?")
          .fadeIn()
          .delay(4000)
          .fadeOut();
      }, 1500);
    } else {
      setTimeout(() => {
        $("#ai-chat-greeting").fadeIn().delay(5000).fadeOut();
      }, 3000);
    }

    console.log("✅ Chatbot initialized successfully");
    return true;
  }

  // ✅ SETUP EVENT HANDLERS - GIỮ NGUYÊN
  function setupEventHandlers() {
    // ✅ FLOATING BUTTON MOUSE DOWN
    $(document).on("mousedown", "#open-ai-chat", function (e) {
      console.log("🖱️ Mouse down on floating button");

      isFloatingBtnDragging = false;
      hasMouseMoved = false;

      if (!floatingWrapper.length) {
        console.error("❌ Floating wrapper not found");
        return;
      }

      const rect = floatingWrapper[0].getBoundingClientRect();
      floatingBtnOffset.x = e.clientX - rect.left;
      floatingBtnOffset.y = e.clientY - rect.top;

      e.preventDefault();

      $(document).on("mousemove.floatingdrag", handleFloatingBtnMove);
      $(document).on("mouseup.floatingdrag", handleFloatingBtnUp);
    });

    function handleFloatingBtnMove(e) {
      if (!hasMouseMoved) {
        const deltaX = Math.abs(
          e.clientX - (floatingBtnOffset.x + floatingWrapper.offset().left)
        );
        const deltaY = Math.abs(
          e.clientY - (floatingBtnOffset.y + floatingWrapper.offset().top)
        );

        if (deltaX > 5 || deltaY > 5) {
          hasMouseMoved = true;
          isFloatingBtnDragging = true;

          console.log("🖱️ Starting floating button drag");

          floatingWrapper.addClass("dragging-floating");
          floatingBtn.addClass("dragging");
          $("body").addClass("dragging-active");
        }
      }

      if (isFloatingBtnDragging) {
        handleFloatingBtnDrag(e);
      }
    }

    function handleFloatingBtnUp(e) {
      console.log("🖱️ Mouse up on floating button", {
        dragging: isFloatingBtnDragging,
        moved: hasMouseMoved,
      });

      $(document).off("mousemove.floatingdrag");
      $(document).off("mouseup.floatingdrag");

      if (isFloatingBtnDragging) {
        floatingWrapper.removeClass("dragging-floating");
        floatingBtn.removeClass("dragging");
        $("body").removeClass("dragging-active");

        saveFloatingBtnPosition();

        setTimeout(() => {
          isFloatingBtnDragging = false;
          hasMouseMoved = false;
        }, 200);
      } else if (!hasMouseMoved) {
        setTimeout(() => {
          openChatModal();
        }, 50);
      }

      hasMouseMoved = false;
    }

    $(document).on("click", "#open-ai-chat", function (e) {
      if (isFloatingBtnDragging || hasMouseMoved) {
        console.log("🚫 Prevented chat open - was dragging");
        e.preventDefault();
        e.stopPropagation();
        return false;
      }

      console.log("🔥 Click detected - opening chat");
      e.preventDefault();
      openChatModal();
    });

    function openChatModal() {
      console.log("🔥 Opening chat modal");
      chatModal.addClass("active");
      chatInput.focus();
    }

    $(document).on("click", ".ai-chat-close", function () {
      console.log("🔥 Closing chat modal");
      chatModal.removeClass("active");
      saveChatPosition();
    });

    $(document).on("click", "#ai-chat-send", function () {
      sendMessage();
    });

    $(document).on("keypress", "#ai-chat-input", function (e) {
      if (e.which === 13 && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
      }
    });

    $(document).on("mousedown", "#ai-chat-header", function (e) {
      if (
        $(e.target).is("button") ||
        $(e.target).closest("button").length > 0
      ) {
        return;
      }

      console.log("🖱️ Starting chat container drag");
      isDragging = true;

      if (!chatContainer.length) {
        console.error("❌ Chat container not found");
        return;
      }

      const rect = chatContainer[0].getBoundingClientRect();
      dragOffset.x = e.clientX - rect.left;
      dragOffset.y = e.clientY - rect.top;

      chatContainer.addClass("dragging");
      $("body").addClass("dragging-active");

      e.preventDefault();
      e.stopPropagation();

      $(document).on("mousemove.chatdrag", handleChatDrag);
      $(document).on("mouseup.chatdrag", stopChatDrag);
    });

    setTimeout(function () {
      if ($("#ai-chat-header .ai-chat-clear").length === 0) {
        $("#ai-chat-title").after(`
          <button class="ai-chat-clear" title="Xóa lịch sử chat">
            <i class="bi bi-trash3"></i>
          </button>
        `);

        $(document).on("click", ".ai-chat-clear", function (e) {
          e.stopPropagation();
          e.preventDefault();
          clearChatHistory();
        });
      }
    }, 2000);

    $(window).on("beforeunload", function () {
      saveGlobalChatHistory();
      saveChatPosition();
      saveFloatingBtnPosition();
    });

    console.log("✅ Event handlers setup complete");
  }

  // ✅ HÀM DRAG - GIỮ NGUYÊN
  function handleFloatingBtnDrag(e) {
    if (!isFloatingBtnDragging || !floatingWrapper.length) return;

    const newX = e.clientX - floatingBtnOffset.x;
    const newY = e.clientY - floatingBtnOffset.y;

    const windowWidth = $(window).width();
    const windowHeight = $(window).height();
    const btnWidth = floatingWrapper.outerWidth();
    const btnHeight = floatingWrapper.outerHeight();

    const limitedX = Math.max(0, Math.min(newX, windowWidth - btnWidth));
    const limitedY = Math.max(0, Math.min(newY, windowHeight - btnHeight));

    floatingWrapper.css({
      position: "fixed",
      left: limitedX + "px",
      top: limitedY + "px",
      right: "auto",
      bottom: "auto",
      transform: "none",
    });

    e.preventDefault();
    e.stopPropagation();
  }

  function handleChatDrag(e) {
    if (!isDragging || !chatContainer.length) return;

    const newX = e.clientX - dragOffset.x;
    const newY = e.clientY - dragOffset.y;

    const windowWidth = $(window).width();
    const windowHeight = $(window).height();
    const chatWidth = chatContainer.outerWidth();
    const chatHeight = chatContainer.outerHeight();

    const limitedX = Math.max(0, Math.min(newX, windowWidth - chatWidth));
    const limitedY = Math.max(0, Math.min(newY, windowHeight - chatHeight));

    chatContainer.css({
      position: "fixed",
      left: limitedX + "px",
      top: limitedY + "px",
      right: "auto",
      bottom: "auto",
      zIndex: "10001",
    });

    e.preventDefault();
    e.stopPropagation();
  }

  function stopChatDrag(e) {
    if (!isDragging) return;

    console.log("🖱️ Stop chat dragging");
    isDragging = false;

    chatContainer.removeClass("dragging");
    $("body").removeClass("dragging-active");

    $(document).off("mousemove.chatdrag");
    $(document).off("mouseup.chatdrag");

    saveChatPosition();
    e.preventDefault();
    e.stopPropagation();
  }

  // ✅ POSITION FUNCTIONS - GIỮ NGUYÊN
  function saveFloatingBtnPosition() {
    if (!floatingWrapper.length) return;

    const currentPos = floatingWrapper.position();
    const currentCSS = {
      left: floatingWrapper.css("left"),
      top: floatingWrapper.css("top"),
    };

    const position = {
      left: currentCSS.left,
      top: currentCSS.top,
      posLeft: currentPos.left,
      posTop: currentPos.top,
      timestamp: Date.now(),
    };

    localStorage.setItem(FLOATING_BTN_POSITION_KEY, JSON.stringify(position));
    console.log("💾 Saved floating btn position:", position);
  }

  function restoreFloatingBtnPosition() {
    try {
      const savedPosition = localStorage.getItem(FLOATING_BTN_POSITION_KEY);
      if (savedPosition && floatingWrapper.length) {
        const position = JSON.parse(savedPosition);

        if (
          position.left &&
          position.left !== "auto" &&
          position.top &&
          position.top !== "auto" &&
          !position.left.includes("NaN") &&
          !position.top.includes("NaN")
        ) {
          const windowWidth = $(window).width();
          const windowHeight = $(window).height();
          const btnWidth = floatingWrapper.outerWidth();
          const btnHeight = floatingWrapper.outerHeight();

          const leftPx = parseInt(position.left) || 0;
          const topPx = parseInt(position.top) || 0;

          const safeLeft = Math.max(
            0,
            Math.min(leftPx, windowWidth - btnWidth)
          );
          const safeTop = Math.max(
            0,
            Math.min(topPx, windowHeight - btnHeight)
          );

          floatingWrapper.css({
            position: "fixed",
            left: safeLeft + "px",
            top: safeTop + "px",
            right: "auto",
            bottom: "auto",
            transform: "none",
          });

          console.log("📍 Restored floating btn position safely:", {
            original: position,
            safe: { left: safeLeft, top: safeTop },
          });
        }
      }
    } catch (e) {
      console.error("❌ Error restoring floating btn position:", e);
      floatingWrapper.css({
        position: "fixed",
        bottom: "80px",
        right: "30px",
        left: "auto",
        top: "auto",
        transform: "none",
      });
    }
  }

  function saveChatPosition() {
    if (!chatContainer.length) return;

    const position = {
      left: chatContainer.css("left"),
      top: chatContainer.css("top"),
      right: chatContainer.css("right"),
      bottom: chatContainer.css("bottom"),
      timestamp: Date.now(),
    };

    localStorage.setItem(CHAT_POSITION_KEY, JSON.stringify(position));
    console.log("💾 Saved chat position:", position);
  }

  function restoreChatPosition() {
    try {
      const savedPosition = localStorage.getItem(CHAT_POSITION_KEY);
      if (savedPosition && chatContainer.length) {
        const position = JSON.parse(savedPosition);

        if (
          position.left &&
          position.left !== "auto" &&
          position.top &&
          position.top !== "auto"
        ) {
          chatContainer.css({
            position: "fixed",
            left: position.left,
            top: position.top,
            right: "auto",
            bottom: "auto",
          });
          console.log("📍 Restored chat position:", position);
        }
      }
    } catch (e) {
      console.error("❌ Error restoring chat position:", e);
    }
  }

  // ✅ API FUNCTIONS - SỬA LẠI VỚI FALLBACK
  function checkLoginStatus() {
    const apiPath = `${apiBasePath}check_session.php`;
    console.log("🔍 Check session API:", apiPath);

    $.ajax({
      url: apiPath,
      method: "POST",
      dataType: "json",
      success: function (response) {
        isUserLoggedIn = response.logged_in || false;
        console.log(
          "👤 Trạng thái đăng nhập:",
          isUserLoggedIn ? "Đã đăng nhập" : "Chưa đăng nhập"
        );
      },
      error: function (xhr, status, error) {
        console.warn("⚠️ Không thể kiểm tra session:", error);
        isUserLoggedIn = false;

        // ✅ FALLBACK - thử path khác
        tryAlternativeApiPath("check_session.php");
      },
    });
  }

  // ✅ THÊM HÀM FALLBACK CHO API PATH
  function tryAlternativeApiPath(fileName) {
    const alternatePaths = [
      `./api/${fileName}`,
      `../api/${fileName}`,
      `../../api/${fileName}`,
      `../../../api/${fileName}`,
    ];

    console.log("🔄 Trying alternative API paths for:", fileName);

    alternatePaths.forEach((path, index) => {
      setTimeout(() => {
        $.ajax({
          url: path,
          method: "POST",
          dataType: "json",
          success: function (response) {
            console.log(`✅ Alternative path works: ${path}`);
          },
          error: function () {
            console.log(`❌ Alternative path failed: ${path}`);
          },
        });
      }, index * 500);
    });
  }

  // ✅ SỬA HÀM saveGlobalChatHistory - LƯU CẢ PRODUCTS
  function saveGlobalChatHistory() {
    const chatData = {
      messages: [],
      timestamp: Date.now(),
      page: window.location.pathname,
    };

    // ✅ DUYỆT QUA TẤT CẢ MESSAGES VÀ PRODUCTS
    let lastBotMessageIndex = -1;

    chatMessages.find(".ai-message").each(function (index) {
      const $this = $(this);
      const isBot = $this.hasClass("ai-bot-message");
      const content = $this.find(".ai-message-content").html();

      const messageData = {
        type: isBot ? "bot" : "user",
        content: content,
        timestamp: Date.now(),
        index: index,
      };

      // ✅ NẾU LÀ BOT MESSAGE THÌ LƯU INDEX
      if (isBot) {
        lastBotMessageIndex = chatData.messages.length;
      }

      chatData.messages.push(messageData);
    });

    // ✅ KIỂM TRA XEM CÓ PRODUCTS ĐANG HIỂN THỊ KHÔNG
    if (
      chatProducts.is(":visible") &&
      chatProducts.find(".ai-product-card").length > 0
    ) {
      console.log("💾 Saving products data with chat history");

      const productsData = [];
      chatProducts.find(".ai-product-card").each(function () {
        const $card = $(this);
        const productData = {
          id:
            $card.attr("onclick").match(/openProductPage\('(\d+)'\)/)?.[1] ||
            "",
          name: $card.find(".ai-product-name").text().trim(),
          price: $card.find(".ai-product-price").text().trim(),
          image_url: $card.find(".ai-product-image").attr("src"),
          score:
            $card.find(".ai-product-score").text().replace("Điểm: ", "") ||
            null,
        };
        productsData.push(productData);
      });

      // ✅ THÊM PRODUCTS VÀO MESSAGE CUỐI CÙNG CỦA BOT
      if (lastBotMessageIndex >= 0 && productsData.length > 0) {
        chatData.messages[lastBotMessageIndex].products = productsData;
        console.log(
          "💾 Products attached to last bot message:",
          productsData.length,
          "items"
        );
      }
    }

    localStorage.setItem(CHAT_STORAGE_KEY, JSON.stringify(chatData));

    if (isUserLoggedIn) {
      const apiPath = `${apiBasePath}chat_history.php`;
      console.log("💾 Saving chat to:", apiPath);

      $.ajax({
        url: apiPath,
        method: "POST",
        data: {
          action: "save",
          messages: chatData.messages,
        },
        dataType: "json",
        success: function (response) {
          if (response.success) {
            console.log("✅ Lưu chat global thành công");
          }
        },
        error: function (xhr, status, error) {
          console.error("❌ Lỗi lưu chat:", error);
        },
      });
    }

    console.log("💾 Saved global chat history with products");
  }

  // ✅ SỬA HÀM loadGlobalChatHistory - LOAD CẢ PRODUCTS
  function loadGlobalChatHistory() {
    console.log("📥 Loading global chat history...");

    try {
      const savedData = localStorage.getItem(CHAT_STORAGE_KEY);
      if (savedData) {
        const chatData = JSON.parse(savedData);

        // ✅ KIỂM TRA THỜI GIAN - 24h thay vì 6h cho products page
        const isProductsPage =
          window.location.pathname.includes("/Page/products/");
        const maxAge = isProductsPage
          ? 24 * 60 * 60 * 1000
          : 6 * 60 * 60 * 1000;

        if (Date.now() - chatData.timestamp < maxAge) {
          console.log("📥 Loading global chat from localStorage");

          // ✅ XÓA TẤT CẢ TRỪ MESSAGE ĐẦU TIÊN
          chatMessages.find(".ai-message").not(":first").remove();
          chatProducts.hide().empty();

          let hasProducts = false;

          chatData.messages.forEach((msg, index) => {
            appendMessage(msg.type, msg.content, false);

            // ✅ NẾU MESSAGE CÓ PRODUCTS THÌ HIỂN THỊ
            if (msg.products && msg.products.length > 0) {
              console.log(
                "📥 Restoring products for message:",
                index,
                msg.products.length,
                "items"
              );
              displayProducts(msg.products);
              hasProducts = true;
            }
          });

          if (hasProducts) {
            console.log("✅ Products restored successfully");
          }

          console.log("✅ Global chat loaded from localStorage");
          return;
        } else {
          localStorage.removeItem(CHAT_STORAGE_KEY);
        }
      }
    } catch (e) {
      console.error("❌ Error loading localStorage chat:", e);
    }

    // ✅ NẾU NGƯỜI DÙNG ĐĂNG NHẬP THÌ LOAD TỪ DATABASE
    if (isUserLoggedIn) {
      const apiPath = `${apiBasePath}chat_history.php`;
      console.log("📥 Loading chat from database:", apiPath);

      $.ajax({
        url: apiPath,
        method: "POST",
        data: { action: "load" },
        dataType: "json",
        timeout: 10000,
        success: function (response) {
          if (
            response.success &&
            response.messages &&
            response.messages.length > 0
          ) {
            console.log("📥 Loading chat from database");
            chatMessages.find(".ai-message").not(":first").remove();
            chatProducts.hide().empty();

            let hasProducts = false;

            response.messages.forEach((msg, index) => {
              appendMessage(msg.type, msg.content, false);

              // ✅ LOAD PRODUCTS TỪ DATABASE
              if (msg.products && msg.products.length > 0) {
                console.log(
                  "📥 Restoring products from DB for message:",
                  index
                );
                displayProducts(msg.products);
                hasProducts = true;
              }
            });

            if (hasProducts) {
              console.log("✅ Products restored from database");
            }

            console.log("✅ Chat loaded from database");
          } else {
            console.log("ℹ️ No chat history found in database");
          }
        },
        error: function (xhr, status, error) {
          console.error("❌ Lỗi tải chat từ database:", error);
        },
      });
    }
  }

  function clearChatHistory() {
    if (
      confirm("Bạn có chắc muốn xóa toàn bộ lịch sử chat trên tất cả trang?")
    ) {
      localStorage.removeItem(CHAT_STORAGE_KEY);

      if (isUserLoggedIn) {
        const apiPath = `${apiBasePath}chat_history.php`;
        console.log("🗑️ Clearing chat via:", apiPath);

        $.ajax({
          url: apiPath,
          method: "POST",
          data: { action: "clear" },
          dataType: "json",
          success: function (response) {
            console.log("🗑️ Xóa chat database thành công");
          },
        });
      }

      chatMessages.find(".ai-message").not(":first").remove();
      chatProducts.hide().empty();
      appendMessage("bot", "🗑️ Đã xóa lịch sử chat trên tất cả trang!", false);
      console.log("🗑️ Cleared global chat history");
    }
  }

  function appendMessage(type, content, saveToStorage = true) {
    const isBot = type === "bot";
    const avatar = isBot ? "🤖" : "👤";

    const messageHtml = `
      <div class="ai-message ${isBot ? "ai-bot-message" : "ai-user-message"}">
        <div class="ai-message-avatar">${avatar}</div>
        <div class="ai-message-content">${content}</div>
      </div>
    `;

    chatMessages.append(messageHtml);
    chatMessages.scrollTop(chatMessages[0].scrollHeight);

    if (saveToStorage) {
      setTimeout(() => {
        saveGlobalChatHistory();
      }, 100);
    }
  }

  // ✅ SỬA HÀM displayProducts - THÊM DELAY SAVE
  function displayProducts(products) {
    if (!products || products.length === 0) return;

    console.log("🖼️ Displaying products:", products.length);

    const html = products
      .map(
        (product) => `
        <div class="ai-product-card" onclick="openProductPage('${product.id}')">
          <img src="${product.image_url}" 
               alt="${product.name || "Sản phẩm hoa"}"
               class="ai-product-image"
               onerror="handleImageError(this, '${product.id}')"
               onload="this.style.opacity='1';"
               style="opacity: 0; transition: opacity 0.3s;">
          <div class="ai-product-info">
            <h4 class="ai-product-name" title="${
              product.name || product.description
            }">
              ${
                product.name ||
                (product.description
                  ? product.description.substring(0, 40) + "..."
                  : "Sản phẩm hoa")
              }
            </h4>
            <p class="ai-product-price">${Number(
              product.price || 0
            ).toLocaleString("vi-VN")}đ</p>
            ${
              product.score
                ? `<small class="ai-product-score" style="color: #6c757d; font-size: 11px;">Điểm: ${product.score}</small>`
                : ""
            }
          </div>
        </div>
      `
      )
      .join("");

    chatProducts.html(`<div class="ai-product-carousel">${html}</div>`);
    chatProducts.show();
    chatMessages.scrollTop(chatMessages[0].scrollHeight);

    // ✅ LƯU LẠI NGAY SAU KHI HIỂN THỊ PRODUCTS
    setTimeout(() => {
      saveGlobalChatHistory();
    }, 500);
  }

  // ✅ CẢI THIỆN HÀM openProductPage
  window.openProductPage = function (productId) {
    console.log("🔗 Opening product page:", productId);

    // ✅ LUU TRẠNG THÁI TRƯỚC KHI CHUYỂN TRANG
    saveGlobalChatHistory();
    saveChatPosition();
    saveFloatingBtnPosition();

    // ✅ ĐÓNG CHAT MODAL
    chatModal.removeClass("active");

    // ✅ CHUYỂN TRANG
    const targetUrl = `${basePath}Page/products/products.php?id=${productId}`;
    console.log("🔗 Navigating to:", targetUrl);

    window.location.href = targetUrl;
  };

  window.handleImageError = function (img, productId) {
    const fallbackImages = [
      `${basePath}uploads/product_${productId}.jpg`,
      `${basePath}img/web/hoahong/hoa_1.jpg`,
      'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="180" height="140"%3E%3Crect fill="%23f8f9fa" width="180" height="140"/%3E%3Ctext x="50%" y="50%" fill="%23999" text-anchor="middle" dy=".3em" font-size="12"%3E🌸 Hình ảnh sản phẩm%3C/text%3E%3C/svg%3E',
    ];

    const currentIndex = parseInt(
      img.getAttribute("data-fallback-index") || "0"
    );
    if (currentIndex < fallbackImages.length) {
      img.setAttribute("data-fallback-index", currentIndex + 1);
      img.src = fallbackImages[currentIndex];
    }
  };

  function addTyping() {
    const typingHtml = `
      <div class="ai-message ai-bot-message ai-typing-message">
        <div class="ai-message-avatar">🤖</div>
        <div class="ai-message-content">
          <div class="ai-typing">
            <span></span><span></span><span></span>
          </div>
        </div>
      </div>
    `;
    chatMessages.append(typingHtml);
    chatMessages.scrollTop(chatMessages[0].scrollHeight);
  }

  function removeTyping() {
    $(".ai-typing-message").remove();
  }

  // ✅ GỬI TIN NHẮN - THÊM FALLBACK API PATHS
  function sendMessage() {
    const message = chatInput.val().trim();
    if (!message) return;

    console.log("📤 Sending message:", message);

    appendMessage("user", message);
    chatInput.val("");
    sendBtn.prop("disabled", true);
    addTyping();

    // ✅ ẨN PRODUCTS CŨ KHI GỬI TIN NHẮN MỚI
    chatProducts.hide();

    // ✅ THỬ NHIỀU API PATHS
    const apiPaths = [
      `${apiBasePath}gemini_chat.php`,
      "./api/gemini_chat.php",
      "../api/gemini_chat.php",
      "../../api/gemini_chat.php",
      "../../../api/gemini_chat.php",
    ];

    let currentPathIndex = 0;

    function tryApiCall() {
      const apiPath = apiPaths[currentPathIndex];
      console.log(
        `🚀 Trying Gemini API (${currentPathIndex + 1}/${apiPaths.length}):`,
        apiPath
      );

      $.ajax({
        url: apiPath,
        method: "POST",
        data: { message: message },
        dataType: "json",
        timeout: 30000,
        success: function (response) {
          removeTyping();
          sendBtn.prop("disabled", false);

          console.log("📥 API Response:", response);

          if (response.success) {
            appendMessage("bot", response.message);
            if (response.products && response.products.length > 0) {
              console.log(
                "🖼️ Displaying new products from API:",
                response.products.length
              );
              displayProducts(response.products);
            }
          } else {
            appendMessage(
              "bot",
              response.message || "Xin lỗi, tôi đang gặp sự cố."
            );
          }
        },
        error: function (xhr, status, error) {
          console.error(`❌ API Error for path ${apiPath}:`, {
            status: status,
            error: error,
            response: xhr.responseText,
          });

          // Thử path tiếp theo
          currentPathIndex++;
          if (currentPathIndex < apiPaths.length) {
            console.log("🔄 Retrying with next API path...");
            setTimeout(tryApiCall, 1000);
          } else {
            // Hết paths để thử
            removeTyping();
            sendBtn.prop("disabled", false);
            appendMessage(
              "bot",
              "⚠️ Lỗi kết nối! Không thể kết nối đến server. Vui lòng kiểm tra lại file gemini_chat.php"
            );
          }
        },
      });
    }

    // Bắt đầu thử API calls
    tryApiCall();
  }

  window.clearChatHistory = clearChatHistory;

  console.log("✅ Chatbot script loaded - Fixed products saving/loading");
});

// ✅ CSS FIXES - GIỮ NGUYÊN
const style = document.createElement("style");
style.textContent = `
  .dragging-active {
    user-select: none !important;
    -webkit-user-select: none !important;
    -moz-user-select: none !important;
    -ms-user-select: none !important;
  }
  
  .dragging-active * {
    user-select: none !important;
    -webkit-user-select: none !important;
    -moz-user-select: none !important;
    -ms-user-select: none !important;
  }

  .ai-chat-floating-wrapper.dragging-floating {
    z-index: 10002 !important;
    transition: none !important;
  }
  
  .ai-chat-floating-btn.dragging {
    cursor: grabbing !important;
    transform: scale(1.1) !important;
    box-shadow: 0 8px 35px rgba(230, 57, 70, 0.8) !important;
    animation: none !important;
  }

  .ai-chat-floating-wrapper {
    transform: none !important;
  }
`;
document.head.appendChild(style);
