<?php
session_start();
session_unset();

$imageData = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Save input text to session
    $_SESSION["text"] = $_POST["text"];

    $apiKey = "Your API Key Here";
    $url = "https://stablediffusionapi.com/api/v3/text2img";

    $width = "512";
    $height = "512";
    $samples = isset($_POST["samples"]) ? $_POST["samples"] : "1";
    $numInferenceSteps = "20";
    $seed = null;
    $guidanceScale = 7.5;
    $safetyChecker = "no";
    $webhook = null;
    $trackId = null;

    $data = [
        "key" => $apiKey,
        "prompt" => $_SESSION["text"],
        "width" => $width,
        "height" => $height,
        "samples" => $samples,
        "num_inference_steps" => $numInferenceSteps,
        "seed" => $seed,
        "guidance_scale" => $guidanceScale,
        "safety_checker" => $safetyChecker,
        "webhook" => $webhook,
        "track_id" => $trackId
    ];

    $options = [
        "http" => [
            "header"  => "Content-type: application/json\r\n",
            "method"  => "POST",
            "content" => json_encode($data),
        ],
    ];

    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === false) {
        echo "Error: Unable to generate image.";
    } else {
        $response = json_decode($result, true);

        if (isset($response["output"][0])) {
            $imageData = $response["output"][0];
        } else {
            echo "Error: Unexpected API response.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Text to Image</title>
</head>
<body>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <label for="text">Enter text:</label>
        <input type="text" name="text" value="<?php echo htmlspecialchars(isset($_SESSION["text"]) ? $_SESSION["text"] : ""); ?>">
        <label for="samples">Samples:</label>
        <input type="number" name="samples" min="1" max="10" value="<?php echo isset($_POST["samples"]) ? $_POST["samples"] : "1"; ?>">
        <button type="submit" name="generate_image">Generate Image</button>
    </form>
    <?php if (!empty($imageData)) { ?>
        <img src='<?php echo $imageData; ?>' alt='Generated Image'>
        <br>
        <a href='<?php echo $imageData; ?>' download>Download Image</a>
    <?php } ?>
</body>
</html>
